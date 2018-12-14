<?php

namespace RentaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Interesado
 *
 * @ORM\Table(name="interesado", indexes={@ORM\Index(name="proceso_id", columns={"proceso_id"})})
 * @ORM\Entity(repositoryClass="RentaBundle\Repository\InteresadoRepository")
 */
class Interesado
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="NIF", type="string", length=9, nullable=false)
     */
    private $nif;

    /**
     * @var string
     *
     * @ORM\Column(name="fichero", type="string", length=255, nullable=true)
     */
    private $fichero;

    /**
     * @var \Proceso
     *
     * @ORM\ManyToOne(targetEntity="Proceso")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="proceso_id", referencedColumnName="id")
     * })
     */
    private $proceso;

    public function getId() {
        return $this->id;
    }

    public function getNif() {
        return $this->nif;
    }

    public function getProceso(): \RentaBundle\Entity\Proceso {
        return $this->proceso;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setNif($nif) {
        $this->nif = $nif;
    }

    public function setProceso(\RentaBundle\Entity\Proceso $proceso) {
        $this->proceso = $proceso;
    }
    public function getFichero() {
        return $this->fichero;
    }

    public function setFichero($fichero) {
        $this->fichero = $fichero;
        return $this;
    }



}

