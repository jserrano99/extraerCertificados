<?php

namespace RentaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Proceso
 *
 * @ORM\Table(name="proceso", indexes={@ORM\Index(name="organismo_id", columns={"organismo_id"}), @ORM\Index(name="fase_id", columns={"fase_id"})})
 * @ORM\Entity
 */
class Proceso {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha", type="date", nullable=false)
     */
    private $fecha;

    /**
     * @var string
     *
     * @ORM\Column(name="fichero", type="string", length=255, nullable=false)
     */
    private $fichero;

    /**
     * @var string
     *
     * @ORM\Column(name="firma", type="string", length=255, nullable=false)
     */
    private $firma;

    /**
     * @var string
     *
     * @ORM\Column(name="descripcion", type="string", length=255, nullable=true)
     */
    private $descripcion;

    /**
     * @var integer
     *
     * @ORM\Column(name="contador", type="integer", nullable=true)
     */
    private $contador;

    /**
     * @var string
     *
     * @ORM\Column(name="zip",  type="string", length=255, nullable=true)
     */
    private $zip;

    /**
     * @var string
     *
     * @ORM\Column(name="texto", type="string", length=255, nullable=true)
     */
    private $texto;

    /**
     * @var \Organismo
     *
     * @ORM\ManyToOne(targetEntity="Organismo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="organismo_id", referencedColumnName="id")
     * })
     */
    private $organismo;

    /**
     * @var \Fase
     *
     * @ORM\ManyToOne(targetEntity="Fase")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fase_id", referencedColumnName="id")
     * })
     */
    private $fase;

    /**
     * @var string
     *
     * @ORM\Column(name="ficheroOriginal",  type="string", length=255, nullable=true)
     */
    private $ficheroOriginal;

    public function getId() {
        return $this->id;
    }

    public function getFecha(): \DateTime {
        return $this->fecha;
    }

    public function getFichero() {
        return $this->fichero;
    }

    public function getFirma() {
        return $this->firma;
    }

    public function getDescripcion() {
        return $this->descripcion;
    }

    public function getContador() {
        return $this->contador;
    }

    public function getZip() {
        return $this->zip;
    }

    public function getTexto() {
        return $this->texto;
    }

    public function getOrganismo(): \RentaBundle\Entity\Organismo {
        return $this->organismo;
    }

    public function getFase(): \RentaBundle\Entity\Fase {
        return $this->fase;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setFecha(\DateTime $fecha) {
        $this->fecha = $fecha;
        return $this;
    }

    public function setFichero($fichero) {
        $this->fichero = $fichero;
        return $this;
    }

    public function setFirma($firma) {
        $this->firma = $firma;
        return $this;
    }

    public function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
        return $this;
    }

    public function setContador($contador) {
        $this->contador = $contador;
        return $this;
    }

    public function setZip($zip) {
        $this->zip = $zip;
        return $this;
    }

    public function setTexto($texto) {
        $this->texto = $texto;
        return $this;
    }

    public function setOrganismo(\RentaBundle\Entity\Organismo $organismo) {
        $this->organismo = $organismo;
        return $this;
    }

    public function setFase(\RentaBundle\Entity\Fase $fase) {
        $this->fase = $fase;
        return $this;
    }

    public function getFicheroOriginal() {
        return $this->ficheroOriginal;
    }

    public function setFicheroOriginal($ficheroOriginal) {
        $this->ficheroOriginal = $ficheroOriginal;
        return $this;
    }

}
