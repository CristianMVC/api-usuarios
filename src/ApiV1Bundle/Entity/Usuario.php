<?php

namespace ApiV1Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Usuario
 *
 * @ORM\Table(name="usuario", uniqueConstraints={@ORM\UniqueConstraint(name="sistema_userid_unique", columns={"sistema", "user_id"})}))
 * @ORM\Entity(repositoryClass="ApiV1Bundle\Repository\UsuarioRepository")
 * @Gedmo\SoftDeleteable(fieldName="fechaBorrado")
 * @ORM\HasLifecycleCallbacks()
 */
class Usuario
{

    const ROL_ADMIN = 1;
    const ROL_ORGANISMO = 2;
    const ROL_AREA = 3;
    const ROL_PUNTOATENCION = 4;
    const ROL_AGENTE = 5;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    //TODO se quita el unique de username para poder mantener usuarios borrados con softdelete. Una posible solución sería mover los usuarios eliminados a otra tabla de historicos y en caso de necesitarlos se realiza un UNION
    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=255)
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=128)
     */
    private $nombre;

    /**
     * @var string
     *
     * @ORM\Column(name="apellido", type="string", length=128)
     */
    private $apellido;

    /**
     * @var int
     *
     * @ORM\Column(name="rol_id", type="smallint")
     */
    private $rol;

    /**
     * @var string
     *
     * @ORM\Column(name="sistema", type="string", length=5)
     */
    private $sistema;

    /**
     * @var int
     *
     * @ORM\Column(name="user_id", type="integer", nullable = true)
     */
    private $userId;

    /**
     * @var int
     *
     * @ORM\Column(name="punto_atencion_id", type="integer", nullable = true)
     */
    private $puntoAtencionId;

    /**
     * @var int
     *
     * @ORM\Column(name="organismo_id", type="integer", nullable = true)
     */
    private $organismoId;

    /**
     * @var int
     *
     * @ORM\Column(name="area_id", type="integer", nullable = true)
     */
    private $areaId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_creado", type="datetimetz")
     */
    private $fechaCreado;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_modificado", type="datetimetz")
     */
    private $fechaModificado;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_borrado", type="datetimetz", nullable=true)
     */
    private $fechaBorrado;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="ultimo_login", type="datetimetz", nullable=true)
     */
    private $ultimoLogin;

    /**
     * Usuario constructor.
     * @param $nombre
     * @param $apellido
     * @param $username
     * @param $rol
     * @param $sistema
     * @param $userId
     */
    public function __construct($nombre, $apellido, $username, $rol, $sistema, $userId=null)
    {
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->rol = $rol;
        $this->username = $username;
        $this->sistema = $sistema;
        $this->userId = $userId;
    }

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
     * Set username
     *
     * @param string $username
     *
     * @return Usuario
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set sistema
     *
     * @param string $sistema
     *
     * @return Usuario
     */
    public function setSistema($sistema)
    {
        $this->sistema = $sistema;

        return $this;
    }

    /**
     * Get sistema
     *
     * @return string
     */
    public function getSistema()
    {
        return $this->sistema;
    }

    /**
     * Set userId
     *
     * @param integer $userId
     *
     * @return Usuario
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @return string
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * @param string $nombre
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
    }

    /**
     * @return string
     */
    public function getApellido()
    {
        return $this->apellido;
    }

    /**
     * @param string $apellido
     */
    public function setApellido($apellido)
    {
        $this->apellido = $apellido;
    }

    /**
     * @return int
     */
    public function getRol()
    {
        return $this->rol;
    }

    /**
     * @param int $rol
     */
    public function setRol($rol)
    {
        $this->rol = $rol;
    }

    /**
     * @return number
     */
    public function getPuntoAtencionId()
    {
        return $this->puntoAtencionId;
    }

    /**
     * @param mixed $puntoAtencionId
     */
    public function setPuntoAtencionId($puntoAtencionId)
    {
        $this->puntoAtencionId = $puntoAtencionId;
    }

    /**
     * @return int
     */
    public function getOrganismoId()
    {
        return $this->organismoId;
    }

    /**
     * Retorna el Organismo Id
     *
     * @param int $organismoId
     */
    public function setOrganismoId($organismoId)
    {
        $this->organismoId = $organismoId;
    }

    /**
     * Retorna el Area Id
     *
     * @return int
     */
    public function getAreaId()
    {
        return $this->areaId;
    }

    /**
     * @param int $areaId
     */
    public function setAreaId($areaId)
    {
        $this->areaId = $areaId;
    }

    /**
     * Set fechaCreado
     *
     * @param \DateTime $fechaCreado
     *
     * @return Usuario
     */
    public function setFechaCreado($fechaCreado)
    {
        $this->fechaCreado = $fechaCreado;

        return $this;
    }

    /**
     * Get fechaCreado
     *
     * @return \DateTime
     */
    public function getFechaCreado()
    {
        return $this->fechaCreado;
    }

    /**
     * Set fechaModificado
     *
     * @param \DateTime $fechaModificado
     *
     * @return Usuario
     */
    public function setFechaModificado($fechaModificado)
    {
        $this->fechaModificado = $fechaModificado;

        return $this;
    }

    /**
     * Get fechaModificado
     *
     * @return \DateTime
     */
    public function getFechaModificado()
    {
        return $this->fechaModificado;
    }

    /**
     * Set fechaBorrado
     *
     * @param \DateTime $fechaBorrado
     *
     * @return Usuario
     */
    public function setFechaBorrado($fechaBorrado)
    {
        $this->fechaBorrado = $fechaBorrado;

        return $this;
    }

    /**
     * Get fechaBorrado
     *
     * @return \DateTime
     */
    public function getFechaBorrado()
    {
        return $this->fechaBorrado;
    }

    /**
     * Set ultimoLogin
     *
     * @param \DateTime $ultimoLogin
     *
     * @return Usuario
     */
    public function setUltimoLogin()
    {
        $this->ultimoLogin = new \DateTime();

        return $this;
    }

    /**
     * Get ultimoLogin
     *
     * @return \DateTime
     */
    public function getUltimoLogin()
    {
        return $this->ultimoLogin;
    }

    /**
     * Genera las fechas de creación y modificación de un organismo
     *
     * @ORM\PrePersist
     */
    public function setFechas()
    {
        $this->fechaCreado = new \DateTime();
        $this->fechaModificado = new \DateTime();
    }

    /**
     * Actualiza la fecha de modificación de un organismo
     *
     * @ORM\PreUpdate
     */
    public function updatedFechas()
    {
        $this->fechaModificado = new \DateTime();
    }
}
