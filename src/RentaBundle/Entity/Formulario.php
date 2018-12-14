<?php

namespace RentaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Formulario
 *
 * @ORM\Table(name="formulario")
 * @ORM\Entity(repositoryClass="RentaBundle\Repository\FormularioRepository")
 */
class Formulario
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="fichero", type="string", length=255)
     */
    private $fichero;

    /**
     * @var string
     *
     * @ORM\Column(name="descripcion", type="string", length=255)
     */
    private $descripcion;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha", type="date", nullable=false)
     */
    private $fecha;

    
    /**
     * @var string
     *
     * @ORM\Column(name="organismo", type="string", length=255)
     */
    private $organismo;

    /**
     * @var string
     *
     * @ORM\Column(name="texto", type="string", length=255)
     */
    private $texto;

    /**
     * @var string
     *
     * @ORM\Column(name="firma", type="string", length=255)
     */
    private $firma;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set fichero
     *
     * @param string $fichero
     *
     * @return Formulario
     */
    public function setFichero($fichero)
    {
        $this->fichero = $fichero;

        return $this;
    }

    /**
     * Get fichero
     *
     * @return string
     */
    public function getFichero()
    {
        return $this->fichero;
    }

    /**
     * Set texto
     *
     * @param string $texto
     *
     * @return Formulario
     */
    public function setTexto($texto)
    {
        $this->texto = $texto;

        return $this;
    }

    /**
     * Get texto
     *
     * @return string
     */
    public function getTexto()
    {
        return $this->texto;
    }

    /**
     * Set firma
     *
     * @param string $firma
     *
     * @return Formulario
     */
    public function setFirma($firma)
    {
        $this->firma = $firma;

        return $this;
    }

    /**
     * Get firma
     *
     * @return string
     */
    public function getFirma()
    {
        return $this->firma;
    }
    
    public function getOrganismo() {
        return $this->organismo;
    }

    public function setOrganismo($organismo) {
        $this->organismo = $organismo;
    }
    public function getDescripcion() {
        return $this->descripcion;
    }

    public function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }

    public function getFecha(): \DateTime {
        return $this->fecha;
    }

    public function setFecha(\DateTime $fecha) {
        $this->fecha = $fecha;
    }



}
