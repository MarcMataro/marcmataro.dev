<?php
/**
 * Classe Usuaris - Gestió completa d'usuaris del sistema
 * 
 * Aquesta classe gestiona totes les operacions CRUD per als usuaris,
 * incloent autenticació, autorització, gestió de tokens i perfils.
 * 
 * @author Marc Mataró
 * @version 1.0
 */

class Usuaris {
    private $connexio;
    private $taula = 'usuaris';
    
    // Rols vàlids per a la validació
    const ROLS_VALIDS = ['superadmin', 'admin', 'editor', 'lector'];
    
    // Configuració de seguretat
    const PASSWORD_MIN_LENGTH = 8;
    const TOKEN_LENGTH = 32;
    const TOKEN_EXPIRY_HOURS = 24;
    const SALT_LENGTH = 16;
    
    /**
     * Constructor
     * 
     * @param PDO $connexio Connexió a la base de dades
     */
    public function __construct($connexio) {
        $this->connexio = $connexio;
    }
    
    /**
     * Crear un nou usuari
     * 
     * @param array $dades Dades de l'usuari
     * @return int|false ID de l'usuari creat o false si hi ha error
     */
    public function crear($dades) {
        try {
            // Validar dades obligatòries
            if (!$this->validarDadesObligatories($dades)) {
                return false;
            }
            
            // Validar format email
            if (!$this->validarEmail($dades['email'])) {
                throw new Exception("Format d'email no vàlid");
            }
            
            // Validar que email i usuari són únics
            if ($this->emailExisteix($dades['email'])) {
                throw new Exception("L'email ja està registrat");
            }
            
            if ($this->usuariExisteix($dades['usuari'])) {
                throw new Exception("El nom d'usuari ja està en ús");
            }
            
            // Validar contrasenya
            if (!$this->validarPassword($dades['password'])) {
                throw new Exception("La contrasenya no compleix els requisits mínims");
            }
            
            // Preparar les dades per a la inserció
            $dadesPreparades = $this->prepararDades($dades);
            
            $sql = "INSERT INTO {$this->taula} (
                nom, cognoms, email, usuari, password_hash, salt, avatar, bio, rol, actiu
            ) VALUES (
                :nom, :cognoms, :email, :usuari, :password_hash, :salt, :avatar, :bio, :rol, :actiu
            )";
            
            $stmt = $this->connexio->prepare($sql);
            
            if ($stmt->execute($dadesPreparades)) {
                return $this->connexio->lastInsertId();
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Error en crear usuari: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log("Error de validació en crear usuari: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtenir un usuari per ID
     * 
     * @param int $id ID de l'usuari
     * @return array|false Dades de l'usuari o false si no existeix
     */
    public function obtenirPerId($id) {
        try {
            $sql = "SELECT * FROM {$this->taula} WHERE id = :id";
            $stmt = $this->connexio->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $usuari = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($usuari) {
                return $this->processarDadesSortida($usuari);
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Error en obtenir usuari per ID: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtenir un usuari per email
     * 
     * @param string $email Email de l'usuari
     * @return array|false Dades de l'usuari o false si no existeix
     */
    public function obtenirPerEmail($email) {
        try {
            $sql = "SELECT * FROM {$this->taula} WHERE email = :email";
            $stmt = $this->connexio->prepare($sql);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            
            $usuari = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($usuari) {
                return $this->processarDadesSortida($usuari);
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Error en obtenir usuari per email: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtenir un usuari per nom d'usuari
     * 
     * @param string $usuari Nom d'usuari
     * @return array|false Dades de l'usuari o false si no existeix
     */
    public function obtenirPerUsuari($usuari) {
        try {
            $sql = "SELECT * FROM {$this->taula} WHERE usuari = :usuari";
            $stmt = $this->connexio->prepare($sql);
            $stmt->bindParam(':usuari', $usuari, PDO::PARAM_STR);
            $stmt->execute();
            
            $usuariData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($usuariData) {
                return $this->processarDadesSortida($usuariData);
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Error en obtenir usuari per nom d'usuari: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Autenticar un usuari
     * 
     * @param string $identificador Email o nom d'usuari
     * @param string $password Contrasenya en text pla
     * @return array|false Dades de l'usuari si l'autenticació és correcta
     */
    public function autenticar($identificador, $password) {
        try {
            // Buscar per email o nom d'usuari
            $sql = "SELECT * FROM {$this->taula} WHERE (email = :identificador OR usuari = :identificador2) AND actiu = 1";
            $stmt = $this->connexio->prepare($sql);
            $stmt->bindParam(':identificador', $identificador, PDO::PARAM_STR);
            $stmt->bindParam(':identificador2', $identificador, PDO::PARAM_STR);
            $stmt->execute();
            
            $usuari = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($usuari && $this->verificarPassword($password, $usuari['password_hash'], $usuari['salt'])) {
                // Actualitzar data d'últim accés
                $this->actualitzarUltimAcces($usuari['id']);
                
                return $this->processarDadesSortida($usuari);
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Error en autenticar usuari: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtenir tots els usuaris amb filtres opcionals
     * 
     * @param array $opcions Opcions de filtrat i ordenació
     * @return array Array d'usuaris
     */
    public function obtenirTots($opcions = []) {
        try {
            $sql = "SELECT * FROM {$this->taula}";
            $parametres = [];
            $condicions = [];
            
            // Aplicar filtres
            if (isset($opcions['rol'])) {
                $condicions[] = "rol = :rol";
                $parametres[':rol'] = $opcions['rol'];
            }
            
            if (isset($opcions['actiu'])) {
                $condicions[] = "actiu = :actiu";
                $parametres[':actiu'] = $opcions['actiu'] ? 1 : 0;
            }
            
            if (isset($opcions['data_registre_desde'])) {
                $condicions[] = "data_registre >= :data_desde";
                $parametres[':data_desde'] = $opcions['data_registre_desde'];
            }
            
            if (isset($opcions['data_registre_fins'])) {
                $condicions[] = "data_registre <= :data_fins";
                $parametres[':data_fins'] = $opcions['data_registre_fins'];
            }
            
            // Afegir condicions WHERE si n'hi ha
            if (!empty($condicions)) {
                $sql .= " WHERE " . implode(" AND ", $condicions);
            }
            
            // Ordenació
            $ordenacio = $opcions['ordre'] ?? 'data_registre DESC';
            $sql .= " ORDER BY " . $ordenacio;
            
            // Límit i offset per a paginació
            if (isset($opcions['limit'])) {
                $sql .= " LIMIT " . (int)$opcions['limit'];
                
                if (isset($opcions['offset'])) {
                    $sql .= " OFFSET " . (int)$opcions['offset'];
                }
            }
            
            $stmt = $this->connexio->prepare($sql);
            $stmt->execute($parametres);
            
            $usuaris = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Processar cada usuari
            return array_map([$this, 'processarDadesSortida'], $usuaris);
            
        } catch (PDOException $e) {
            error_log("Error en obtenir usuaris: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtenir usuaris actius
     * 
     * @param int $limit Límit de resultats
     * @return array Array d'usuaris actius
     */
    public function obtenirActius($limit = null) {
        $opcions = [
            'actiu' => true,
            'ordre' => 'data_ultim_acces DESC, data_registre DESC'
        ];
        
        if ($limit) {
            $opcions['limit'] = $limit;
        }
        
        return $this->obtenirTots($opcions);
    }
    
    /**
     * Actualitzar un usuari
     * 
     * @param int $id ID de l'usuari
     * @param array $dades Noves dades
     * @return bool True si s'ha actualitzat correctament
     */
    public function actualitzar($id, $dades) {
        try {
            // Verificar que l'usuari existeix
            if (!$this->obtenirPerId($id)) {
                return false;
            }
            
            // Si s'actualitza l'email, verificar que és únic
            if (isset($dades['email']) && $this->emailExisteix($dades['email'], $id)) {
                throw new Exception("L'email ja està en ús per un altre usuari");
            }
            
            // Si s'actualitza l'usuari, verificar que és únic
            if (isset($dades['usuari']) && $this->usuariExisteix($dades['usuari'], $id)) {
                throw new Exception("El nom d'usuari ja està en ús");
            }
            
            // Si s'actualitza la contrasenya, processar-la
            if (isset($dades['password'])) {
                if (!$this->validarPassword($dades['password'])) {
                    throw new Exception("La contrasenya no compleix els requisits mínims");
                }
            }
            
            // Preparar les dades
            $dadesPreparades = $this->prepararDades($dades, false);
            
            // Construir la consulta dinàmicament
            $camps = [];
            foreach (array_keys($dadesPreparades) as $camp) {
                $campSensePrefix = ltrim($camp, ':');
                $camps[] = "{$campSensePrefix} = {$camp}";
            }
            
            $sql = "UPDATE {$this->taula} SET " . implode(', ', $camps) . " WHERE id = :id";
            $dadesPreparades[':id'] = $id;
            
            $stmt = $this->connexio->prepare($sql);
            return $stmt->execute($dadesPreparades);
            
        } catch (PDOException $e) {
            error_log("Error en actualitzar usuari: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log("Error de validació en actualitzar usuari: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar un usuari (desactivar)
     * 
     * @param int $id ID de l'usuari
     * @param bool $eliminacioFisica Si true, elimina físicament el registre
     * @return bool True si s'ha eliminat correctament
     */
    public function eliminar($id, $eliminacioFisica = false) {
        try {
            if ($eliminacioFisica) {
                $sql = "DELETE FROM {$this->taula} WHERE id = :id";
            } else {
                $sql = "UPDATE {$this->taula} SET actiu = 0 WHERE id = :id";
            }
            
            $stmt = $this->connexio->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en eliminar usuari: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Canviar contrasenya d'un usuari
     * 
     * @param int $id ID de l'usuari
     * @param string $novaPassword Nova contrasenya en text pla
     * @return bool True si s'ha canviat correctament
     */
    public function canviarPassword($id, $novaPassword) {
        try {
            if (!$this->validarPassword($novaPassword)) {
                throw new Exception("La contrasenya no compleix els requisits mínims");
            }
            
            $salt = $this->generarSalt();
            $passwordHash = $this->hashPassword($novaPassword, $salt);
            
            $sql = "UPDATE {$this->taula} SET password_hash = :password_hash, salt = :salt WHERE id = :id";
            $stmt = $this->connexio->prepare($sql);
            
            return $stmt->execute([
                ':password_hash' => $passwordHash,
                ':salt' => $salt,
                ':id' => $id
            ]);
            
        } catch (PDOException $e) {
            error_log("Error en canviar contrasenya: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log("Error de validació en canviar contrasenya: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generar token de restabliment de contrasenya
     * 
     * @param string $email Email de l'usuari
     * @return string|false Token generat o false si hi ha error
     */
    public function generarTokenRestabliment($email) {
        try {
            $usuari = $this->obtenirPerEmail($email);
            if (!$usuari || !$usuari['actiu']) {
                return false;
            }
            
            $token = $this->generarToken();
            $dataExpiracio = date('Y-m-d H:i:s', time() + (self::TOKEN_EXPIRY_HOURS * 3600));
            
            $sql = "UPDATE {$this->taula} SET token_restabliment = :token, data_expiracion_token = :data_expiracio WHERE id = :id";
            $stmt = $this->connexio->prepare($sql);
            
            if ($stmt->execute([
                ':token' => $token,
                ':data_expiracio' => $dataExpiracio,
                ':id' => $usuari['id']
            ])) {
                return $token;
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Error en generar token de restabliment: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Restablir contrasenya amb token
     * 
     * @param string $token Token de restabliment
     * @param string $novaPassword Nova contrasenya
     * @return bool True si s'ha restablit correctament
     */
    public function restablirPassword($token, $novaPassword) {
        try {
            if (!$this->validarPassword($novaPassword)) {
                throw new Exception("La contrasenya no compleix els requisits mínims");
            }
            
            // Buscar usuari amb token vàlid
            $sql = "SELECT id FROM {$this->taula} 
                    WHERE token_restabliment = :token 
                    AND data_expiracion_token > NOW() 
                    AND actiu = 1";
            
            $stmt = $this->connexio->prepare($sql);
            $stmt->bindParam(':token', $token, PDO::PARAM_STR);
            $stmt->execute();
            
            $usuari = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$usuari) {
                return false;
            }
            
            // Canviar contrasenya i eliminar token
            $salt = $this->generarSalt();
            $passwordHash = $this->hashPassword($novaPassword, $salt);
            
            $sql = "UPDATE {$this->taula} 
                    SET password_hash = :password_hash, 
                        salt = :salt, 
                        token_restabliment = NULL, 
                        data_expiracion_token = NULL 
                    WHERE id = :id";
            
            $stmt = $this->connexio->prepare($sql);
            
            return $stmt->execute([
                ':password_hash' => $passwordHash,
                ':salt' => $salt,
                ':id' => $usuari['id']
            ]);
            
        } catch (PDOException $e) {
            error_log("Error en restablir contrasenya: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log("Error de validació en restablir contrasenya: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Buscar usuaris per text
     * 
     * @param string $terme Terme de cerca
     * @param array $opcions Opcions addicionals
     * @return array Array d'usuaris trobats
     */
    public function buscar($terme, $opcions = []) {
        try {
            $sql = "SELECT * FROM {$this->taula} 
                    WHERE (nom LIKE :terme1 
                           OR cognoms LIKE :terme2 
                           OR email LIKE :terme3 
                           OR usuari LIKE :terme4
                           OR bio LIKE :terme5)";
            
            $parametres = [
                ':terme1' => "%{$terme}%",
                ':terme2' => "%{$terme}%",
                ':terme3' => "%{$terme}%",
                ':terme4' => "%{$terme}%",
                ':terme5' => "%{$terme}%"
            ];
            
            // Afegir filtres addicionals
            if (isset($opcions['actiu']) && $opcions['actiu']) {
                $sql .= " AND actiu = 1";
            }
            
            if (isset($opcions['rol'])) {
                $sql .= " AND rol = :rol";
                $parametres[':rol'] = $opcions['rol'];
            }
            
            $sql .= " ORDER BY data_registre DESC";
            
            if (isset($opcions['limit'])) {
                $sql .= " LIMIT " . (int)$opcions['limit'];
            }
            
            $stmt = $this->connexio->prepare($sql);
            $stmt->execute($parametres);
            
            $usuaris = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return array_map([$this, 'processarDadesSortida'], $usuaris);
            
        } catch (PDOException $e) {
            error_log("Error en buscar usuaris: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Comptar usuaris amb filtres opcionals
     * 
     * @param array $opcions Opcions de filtrat
     * @return int Nombre d'usuaris
     */
    public function comptar($opcions = []) {
        try {
            $sql = "SELECT COUNT(*) FROM {$this->taula}";
            $parametres = [];
            $condicions = [];
            
            // Aplicar els mateixos filtres que obtenirTots
            if (isset($opcions['rol'])) {
                $condicions[] = "rol = :rol";
                $parametres[':rol'] = $opcions['rol'];
            }
            
            if (isset($opcions['actiu'])) {
                $condicions[] = "actiu = :actiu";
                $parametres[':actiu'] = $opcions['actiu'] ? 1 : 0;
            }
            
            if (!empty($condicions)) {
                $sql .= " WHERE " . implode(" AND ", $condicions);
            }
            
            $stmt = $this->connexio->prepare($sql);
            $stmt->execute($parametres);
            
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en comptar usuaris: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Obtenir estadístiques dels usuaris
     * 
     * @return array Estadístiques
     */
    public function obtenirEstadistiques() {
        try {
            $sql = "SELECT 
                        COUNT(*) as total,
                        COUNT(CASE WHEN actiu = 1 THEN 1 END) as actius,
                        COUNT(CASE WHEN rol = 'superadmin' THEN 1 END) as superadmins,
                        COUNT(CASE WHEN rol = 'admin' THEN 1 END) as admins,
                        COUNT(CASE WHEN rol = 'editor' THEN 1 END) as editors,
                        COUNT(CASE WHEN rol = 'lector' THEN 1 END) as lectors,
                        COUNT(CASE WHEN data_ultim_acces > DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as actius_ultim_mes
                    FROM {$this->taula}";
            
            $stmt = $this->connexio->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenir estadístiques: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Actualitzar data d'últim accés
     * 
     * @param int $id ID de l'usuari
     * @return bool True si s'ha actualitzat correctament
     */
    private function actualitzarUltimAcces($id) {
        try {
            $sql = "UPDATE {$this->taula} SET data_ultim_acces = NOW() WHERE id = :id";
            $stmt = $this->connexio->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en actualitzar últim accés: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validar dades obligatòries
     * 
     * @param array $dades Dades a validar
     * @return bool True si les dades són vàlides
     */
    private function validarDadesObligatories($dades) {
        $campsObligatoris = ['nom', 'cognoms', 'email', 'usuari', 'password'];
        
        foreach ($campsObligatoris as $camp) {
            if (empty($dades[$camp])) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Validar format d'email
     * 
     * @param string $email Email a validar
     * @return bool True si l'email és vàlid
     */
    private function validarEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validar contrasenya
     * 
     * @param string $password Contrasenya a validar
     * @return bool True si la contrasenya és vàlida
     */
    private function validarPassword($password) {
        // Mínim 8 caràcters, almenys una lletra i un número
        return strlen($password) >= self::PASSWORD_MIN_LENGTH &&
               preg_match('/[a-zA-Z]/', $password) &&
               preg_match('/[0-9]/', $password);
    }
    
    /**
     * Verificar si un email ja existeix
     * 
     * @param string $email Email a verificar
     * @param int $excloureId ID a excloure (per a actualitzacions)
     * @return bool True si existeix
     */
    private function emailExisteix($email, $excloureId = null) {
        try {
            $sql = "SELECT id FROM {$this->taula} WHERE email = :email";
            $parametres = [':email' => $email];
            
            if ($excloureId) {
                $sql .= " AND id != :id";
                $parametres[':id'] = $excloureId;
            }
            
            $stmt = $this->connexio->prepare($sql);
            $stmt->execute($parametres);
            
            return $stmt->fetchColumn() !== false;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Verificar si un nom d'usuari ja existeix
     * 
     * @param string $usuari Nom d'usuari a verificar
     * @param int $excloureId ID a excloure (per a actualitzacions)
     * @return bool True si existeix
     */
    private function usuariExisteix($usuari, $excloureId = null) {
        try {
            $sql = "SELECT id FROM {$this->taula} WHERE usuari = :usuari";
            $parametres = [':usuari' => $usuari];
            
            if ($excloureId) {
                $sql .= " AND id != :id";
                $parametres[':id'] = $excloureId;
            }
            
            $stmt = $this->connexio->prepare($sql);
            $stmt->execute($parametres);
            
            return $stmt->fetchColumn() !== false;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Generar hash de contrasenya
     * 
     * @param string $password Contrasenya en text pla
     * @param string $salt Salt per al hash
     * @return string Hash de la contrasenya
     */
    private function hashPassword($password, $salt) {
        return hash('sha256', $salt . $password . $salt);
    }
    
    /**
     * Verificar contrasenya
     * 
     * @param string $password Contrasenya en text pla
     * @param string $hash Hash emmagatzemat
     * @param string $salt Salt utilitzat
     * @return bool True si la contrasenya és correcta
     */
    private function verificarPassword($password, $hash, $salt) {
        return hash_equals($hash, $this->hashPassword($password, $salt));
    }
    
    /**
     * Generar salt aleatori
     * 
     * @return string Salt generat
     */
    private function generarSalt() {
        return bin2hex(random_bytes(self::SALT_LENGTH));
    }
    
    /**
     * Generar token aleatori
     * 
     * @return string Token generat
     */
    private function generarToken() {
        return bin2hex(random_bytes(self::TOKEN_LENGTH));
    }
    
    /**
     * Preparar dades per a la base de dades
     * 
     * @param array $dades Dades originals
     * @param bool $esNou Si és un registre nou (per defectes)
     * @return array Dades preparades
     */
    private function prepararDades($dades, $esNou = true) {
        $dadesPreparades = [];
        
        // Camps de text obligatoris
        $campsText = ['nom', 'cognoms', 'email', 'usuari', 'avatar', 'bio'];
        
        foreach ($campsText as $camp) {
            if (isset($dades[$camp])) {
                $dadesPreparades[":{$camp}"] = $dades[$camp] ?: null;
            } elseif ($esNou && in_array($camp, ['nom', 'cognoms', 'email', 'usuari'])) {
                $dadesPreparades[":{$camp}"] = $dades[$camp];
            } elseif ($esNou) {
                $dadesPreparades[":{$camp}"] = null;
            }
        }
        
        // Contrasenya (només si s'especifica)
        if (isset($dades['password'])) {
            $salt = $this->generarSalt();
            $dadesPreparades[':password_hash'] = $this->hashPassword($dades['password'], $salt);
            $dadesPreparades[':salt'] = $salt;
        }
        
        // Rol amb validació
        if (isset($dades['rol'])) {
            if (in_array($dades['rol'], self::ROLS_VALIDS)) {
                $dadesPreparades[':rol'] = $dades['rol'];
            }
        } elseif ($esNou) {
            $dadesPreparades[':rol'] = 'editor';
        }
        
        // Actiu (boolean)
        if (isset($dades['actiu'])) {
            $dadesPreparades[':actiu'] = $dades['actiu'] ? 1 : 0;
        } elseif ($esNou) {
            $dadesPreparades[':actiu'] = 1;
        }
        
        return $dadesPreparades;
    }
    
    /**
     * Processar dades de sortida
     * 
     * @param array $usuari Dades de l'usuari
     * @return array Dades processades
     */
    private function processarDadesSortida($usuari) {
        // Convertir actiu a boolean
        $usuari['actiu'] = (bool)$usuari['actiu'];
        
        // Eliminar informació sensible
        unset($usuari['password_hash']);
        unset($usuari['salt']);
        unset($usuari['token_restabliment']);
        unset($usuari['data_expiracion_token']);
        
        return $usuari;
    }
    
    /**
     * Obtenir rols vàlids
     * 
     * @return array Rols vàlids
     */
    public static function getRolsValids() {
        return self::ROLS_VALIDS;
    }
    
    /**
     * Verificar si un usuari té un rol específic o superior
     * 
     * @param string $rolUsuari Rol de l'usuari
     * @param string $rolRequerit Rol requerit
     * @return bool True si té el rol o superior
     */
    public static function teRol($rolUsuari, $rolRequerit) {
        $jerarquia = [
            'lector' => 1,
            'editor' => 2,
            'admin' => 3,
            'superadmin' => 4
        ];
        
        return isset($jerarquia[$rolUsuari]) && 
               isset($jerarquia[$rolRequerit]) && 
               $jerarquia[$rolUsuari] >= $jerarquia[$rolRequerit];
    }
}
?>
