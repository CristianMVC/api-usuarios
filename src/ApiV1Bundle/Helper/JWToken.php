<?php
/**
 * JWToken class
 * @author Fausto Carrera <fcarrera@hexacta.com>
 * Docs: https://github.com/lcobucci/jwt/blob/3.2/README.md
 */
namespace ApiV1Bundle\Helper;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\ValidationData;
use Symfony\Component\DependencyInjection\Container;

class JWToken
{
    private $secret;
    private $builder;
    private $parser;
    private $signer;
    private $validationData;
    private $isValid = false;
    private $roles = null;
    private $ttl = 7200;
    private $domain;

    public function __construct(
        Container $container,
        $secret,
        Builder $builder,
        Parser $parser,
        ValidationData $validationData
    ) {
        $this->secret = $secret;
        $this->token = $builder;
        $this->parser = $parser;
        $this->validationData = $validationData;
        $this->signer = new Sha256();
        $this->domain = $container->getParameter('jwt_domain');
    }

    /**
     * @param $rol
     * @return int
     */
    private function getTtl($rol)
    {
        switch ($rol) {
            case 'ROL_PUNTOATENCION':
            case 'ROL_AGENTE':
                return 28800;
                break;
            default:
                return 7200;
                break;
        }
    }

    /**
     * Generar JWToken
     *
     * @return \Lcobucci\JWT\Builder
     */
    public function getToken($uid, $username, $role, $puntoAtencion=null)
    {
        $token = $this->token;
        $token->setIssuer($this->getDomain());
        $token->setAudience($this->getDomain());
        $token->setIssuedAt(time());
        $token->setId(md5($this->secret . $this->getDomain()));
        $token->set('timestamp', time());
        $token->set('uid', $uid);
        $token->set('username', $username);
        $token->set('role', $role);
        if (!is_null($puntoAtencion)) {
            $token->set('puntoatencion', $puntoAtencion);
        }
        $token->setExpiration(time() + $this->getTtl($role));
        $token->sign($this->signer, $this->secret);
        return (string) $token->getToken();
    }

    /**
     * Validar token
     *
     * @param $tokenString
     * @return boolean
     */
    public function validate($tokenString, $tokenCancelado)
    {
        try {
            $token = $this->parseToken($tokenString);
            $isValid = $token->validate($this->validationData());
            if ($isValid && is_null($tokenCancelado)) {
                // verify the token signature
                if ($token->verify($this->signer, $this->secret)) {
                    $this->isValid = $isValid;
                    $this->role = $token->getClaim('role');
                }
            }
        } catch (\Exception $e) {
            // do nothing
        }
        return $this;
    }

    /**
     * Is token valid
     *
     * @return boolean
     */
    public function isValid()
    {
        return $this->isValid;
    }

    /**
     * Rol saved on the token
     *
     * @return string
     */
    public function getRol()
    {
        return $this->role;
    }

    /**
     * Parsear token
     *
     * @param $token
     * @return \Lcobucci\JWT\Token
     */
    private function parseToken($token)
    {
        return $this->parser->parse((string) $token);
    }

    /**
     * Datos para validar un token
     *
     * @return \Lcobucci\JWT\ValidationData
     */
    private function validationData()
    {
        $this->validationData->setIssuer($this->getDomain());
        $this->validationData->setAudience($this->getDomain());
        $this->validationData->setId(md5($this->secret . $this->getDomain()));
        return $this->validationData;
    }

    /**
     * Obtener el dominio que genera el token
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Obtenemos el unique id del token
     * @param $token
     * @return mixed
     */
    public static function getUid($token)
    {
        $parsed = (new Parser())->parse($token);
        return $parsed->getClaim('uid', NAN);
    }

    /**
     * @param $token
     * @return mixed
     */
    public static function getRoles($token)
    {
        $parsed = (new Parser())->parse($token);
        return $parsed->getClaim('role', NAN);
    }
}
