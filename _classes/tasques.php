<?php
/**
 * Classe TascaDiaria
 * 
 * Representa una tasca diària segons l'estructura de la taula `tasques_diaries`.
 * Proporciona mètodes per gestionar i manipular tasques: creació, lectura, actualització i eliminació (CRUD).
 *
 * @author Marc Mataró
 */
class TascaDiaria {
    /** @var int|null ID únic de la tasca */
    public $id;
    /** @var string Títol de la tasca */
    public $titol;
    /** @var string Descripció de la tasca */
    public $descripcio;
    /** @var string Data de la tasca (YYYY-MM-DD) */
    public $data_tasca;
    /** @var string|null Hora d'inici (HH:MM:SS) */
    public $hora_inici;
    /** @var string|null Hora de finalització (HH:MM:SS) */
    public $hora_fi;
    /** @var string Prioritat ('baixa', 'mitjana', 'alta') */
    public $prioritat;
    /** @var string Estat ('pendent', 'en_progres', 'completada', 'cancelada') */
    public $estat;
    /** @var string Categoria ('treball', 'personal', 'salut', 'formacio', 'llar', 'social', 'altres') */
    public $categoria;
    /** @var string|null Data de creació (YYYY-MM-DD HH:MM:SS) */
    public $data_creacio;
    /** @var string|null Data d'actualització (YYYY-MM-DD HH:MM:SS) */
    public $data_actualitzacio;
    /** @var bool Indica si la tasca és important */
    public $es_important;
    /** @var bool Indica si la tasca és urgent */
    public $es_urgent;

    /**
     * Constructor de la classe
     * @param array|null $row Array associatiu amb les dades de la tasca (opcional)
     */
    public function __construct($row = null) {
        if ($row) {
            $this->id = $row['id'] ?? null;
            $this->titol = $row['titol'] ?? '';
            $this->descripcio = $row['descripcio'] ?? '';
            $this->data_tasca = $row['data_tasca'] ?? null;
            $this->hora_inici = $row['hora_inici'] ?? null;
            $this->hora_fi = $row['hora_fi'] ?? null;
            $this->prioritat = $row['prioritat'] ?? 'mitjana';
            $this->estat = $row['estat'] ?? 'pendent';
            $this->categoria = $row['categoria'] ?? 'treball';
            $this->data_creacio = $row['data_creacio'] ?? null;
            $this->data_actualitzacio = $row['data_actualitzacio'] ?? null;
            $this->es_important = (bool)($row['es_important'] ?? false);
            $this->es_urgent = (bool)($row['es_urgent'] ?? false);
        }
    }

    /**
     * Retorna l'estat de la tasca en format llegible
     * @return string
     */
    public function getEstatLabel() {
        switch ($this->estat) {
            case 'pendent': return 'Pendent';
            case 'en_progres': return 'En progrés';
            case 'completada': return 'Completada';
            case 'cancelada': return 'Cancel·lada';
            default: return $this->estat;
        }
    }

    /**
     * Retorna la prioritat de la tasca en format llegible
     * @return string
     */
    public function getPrioritatLabel() {
        switch ($this->prioritat) {
            case 'baixa': return 'Baixa';
            case 'mitjana': return 'Mitjana';
            case 'alta': return 'Alta';
            default: return $this->prioritat;
        }
    }

    /**
     * Retorna la categoria de la tasca en format llegible
     * @return string
     */
    public function getCategoriaLabel() {
        switch ($this->categoria) {
            case 'treball': return 'Treball';
            case 'personal': return 'Personal';
            case 'salut': return 'Salut';
            case 'formacio': return 'Formació';
            case 'llar': return 'Llar';
            case 'social': return 'Social';
            case 'altres': return 'Altres';
            default: return $this->categoria;
        }
    }

    /**
     * Retorna si la tasca és important i/o urgent en format llegible
     * @return string
     */
    public function getImportantUrgentLabel() {
        if ($this->es_important && $this->es_urgent) return 'Important i urgent';
        if ($this->es_important) return 'Important';
        if ($this->es_urgent) return 'Urgent';
        return '';
    }

    /**
     * Converteix l'objecte a array associatiu (per a inserts/updates)
     * @return array
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'titol' => $this->titol,
            'descripcio' => $this->descripcio,
            'data_tasca' => $this->data_tasca,
            'hora_inici' => $this->hora_inici,
            'hora_fi' => $this->hora_fi,
            'prioritat' => $this->prioritat,
            'estat' => $this->estat,
            'categoria' => $this->categoria,
            'data_creacio' => $this->data_creacio,
            'data_actualitzacio' => $this->data_actualitzacio,
            'es_important' => $this->es_important,
            'es_urgent' => $this->es_urgent,
        ];
    }
}