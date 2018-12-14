<?php

namespace RentaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Usuario
 *
 * @ORM\Table(name="usuario", indexes={@ORM\Index(name="idx_modo", columns={"modo_id"})})
 * @ORM\Entity
 */
class Usuario
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
     * @ORM\Column(name="codigo", type="string", length=15, nullable=false)
     */
    private $codigo;

    /**
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=255, nullable=false)
     */
    private $nombre;

    /**
     * @var string
     *
     * @ORM\Column(name="apellidos", type="string", length=255, nullable=false)
     */
    private $apellidos;

    /**
     * @var string
     *
     * @ORM\Column(name="perfil", type="string", length=25, nullable=false)
     */
    private $perfil;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=false)
     */
    private $email;

    /**
     * @var integer
     *
     * @ORM\Column(name="estado", type="integer", nullable=false)
     */
    private $estado;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255, nullable=false)
     */
    private $password;

    /**
     * @var integer
     *
     * @ORM\Column(name="modo_id", type="integer", nullable=false)
     */
    private $modoId = '1';
    public function getId() {
        return $this->id;
    }

    public function getCodigo() {
        return $this->codigo;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function getApellidos() {
        return $this->apellidos;
    }

    public function getPerfil() {
        return $this->perfil;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getEstado() {
        return $this->estado;
    }

    public function getPassword() {
        return $this->password;
    }

    public function getModoId() {
        return $this->modoId;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setCodigo($codigo) {
        $this->codigo = $codigo;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    public function setApellidos($apellidos) {
        $this->apellidos = $apellidos;
    }

    public function setPerfil($perfil) {
        $this->perfil = $perfil;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function setEstado($estado) {
        $this->estado = $estado;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function setModoId($modoId) {
        $this->modoId = $modoId;
    }



}

