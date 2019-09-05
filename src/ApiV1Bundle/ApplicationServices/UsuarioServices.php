<?php

namespace ApiV1Bundle\ApplicationServices;
use ApiV1Bundle\Entity\Factory\UsuarioFactory;
use ApiV1Bundle\Entity\Sync\UsuarioListado;
use ApiV1Bundle\Entity\Usuario;
use ApiV1Bundle\Entity\ValidateResultado;
use ApiV1Bundle\Entity\Validator\UsuarioValidator;
use ApiV1Bundle\ExternalServices\UsuarioIntegration;
use ApiV1Bundle\Repository\UsuarioRepository;
use Symfony\Component\DependencyInjection\Container;
use ApiV1Bundle\Entity\Sync\UsuarioSync;
use Unirest\Exception;

/**
 * Class UsuarioServices
 * @package ApiV1Bundle\ApplicationServices
 */
class UsuarioServices extends SNTUserServices
{
    /**
     * Integration service
     */
    private $usuarioIntegration;

    /**
     * User repository
     */
    private $usuarioRepository;

    /**
     * Usuario validator
     */
    private $usuarioValidator;

    /**
     * Verificar usuario
     */
    private $verificarUsuario;

    /**
     * @var RolesServices
     */
    private $rolesServices;

    /**
     * UsuarioServices constructor.
     */
    public function __construct(
        Container $container,
        UsuarioIntegration $usuarioIntegration,
        UsuarioRepository $usuarioRepository,
        UsuarioValidator $usuarioValidator,
        VerificarUsuarioServices $verificarUsuarioServices,
        RolesServices $rolesServices
    ) {
        parent::__construct($container);
        $this->usuarioIntegration = $usuarioIntegration;
        $this->usuarioRepository = $usuarioRepository;
        $this->usuarioValidator = $usuarioValidator;
        $this->verificarUsuario = $verificarUsuarioServices;
        $this->rolesServices = $rolesServices;
    }

    /**
     * listar usuarios del SNT/SNC
     *
     * @param string $sistema 'snt'|'snc'
     * @return mixed
     */
    public function findAll($sistema, $authorization)
    {
        return $this->usuarioIntegration->findAll($sistema, $authorization);
    }

    /**
     * Obtener un usuario
     *
     * @param integer $id ID de usuario en SNU
     * @return object
     */
    public function get($usuarioId, $rolId, $authorization)
    {
        // verificamos si el usuario está en la base local
        $usuario = $this->usuarioRepository->findByUserIdRolId($usuarioId, $rolId);
        $validateResultado = $this->usuarioValidator->validarUsuario($usuario);
        if (! $validateResultado->hasError()) {
            $result = $this->verificarUsuario->verificarUsuario($usuario, $authorization);
            return $this->respuestaData([], $result);
        }
        return $this->respuestaData([], null);
    }

    /**
     * Edita un usuario
     *
     * @param array $params Arreglo con los datos a modificar
     * @param integer $id Identificador único de usuario
     * @param $success funcion que devuelve si tuvo éxito
     * @param $error funcion que devuelve si ocurrio un error
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function edit($params, $usuarioId, $rolId, $authorization, $success, $error)
    {
        $validateResult = $this->usuarioValidator->validarEdit($params);
        if (!$validateResult->hasError()) {
            $usuario = $this->usuarioRepository->findByUserIdRolId($usuarioId, $rolId);
            $validateResult = $this->usuarioValidator->validarUsuario($usuario);
            if (!$validateResult->hasError()) {
                $this->usuarioRepository->beginTransaction();
                $userSync = new UsuarioSync($this->usuarioRepository, $this->usuarioValidator);
                $validateResult = $userSync->edit($usuario->getId(), $params);
                if (!$validateResult->hasError()) {
                    try {
                        $validateResult = $this->usuarioIntegration->edit($usuario, $params, $authorization);
                        if (! $validateResult->hasError()) {
                            $this->usuarioRepository->flush();
                            $this->usuarioRepository->commit();
                        }else{
                            $this->usuarioRepository->rollback();
                        }
                    } catch (Exception $exception) {
                        $this->usuarioRepository->rollback();
                        $validateResult = new ValidateResultado(null, [$exception->getMessage()]);
                    }
                }
            }
        }
        return $this->processResult(
            $validateResult,
            function () use ($success) {
                return call_user_func($success);
            },
            $error
        );
    }

    /**
     * Eliminar un  usuario
     *
     * @param integer $usuarioId ID de usuario en SNU
     * @param $usuarioId
     * @param $success
     * @param $error
     * @return mixed
     */
    public function delete($usuarioId, $rolId, $authorization, $success, $error)
    {
        $usuario = $this->usuarioRepository->findByUserIdRolId($usuarioId, $rolId);
        $validateResult = $this->usuarioValidator->validarUsuario($usuario);
        if (! $validateResult->hasError()) {
            try {
                $validateResult = $this->usuarioIntegration->delete($usuario, $authorization);
                if (!$validateResult->hasError()) {
                    $validateResult->setEntity($usuario);
                }
            } catch (Exception $exception) {
                $validateResult = new ValidateResultado(null, [$exception->getMessage()]);
            }
        }
        return $this->processResult(
            $validateResult,
            function ($entity) use ($success) {
                return call_user_func($success, $this->usuarioRepository->remove($entity));
            },
            $error
        );
    }

    /**
     * Crear un usuario
     *
     * @param $params
     * @param $success
     * @param $error
     * @return mixed
     */
    public function create($params, $authorization, $success, $error)
    {
        $usuario = $this->usuarioRepository->findOneByUsername($params['username']);
        $validateResult = $this->usuarioValidator->validarAddUsuario($params, $usuario);
        if (! $validateResult->hasError()) {
            $usuarioFactory = new UsuarioFactory(
                $this->usuarioValidator,
                $this->usuarioIntegration
            );
            $this->usuarioRepository->beginTransaction();
            $sistema = $usuarioFactory->getSistema($params);
            $validateResult = $usuarioFactory->create($params, $sistema);
            if (! $validateResult->hasError()) {
                try {
                    $usuario = $validateResult->getEntity();
                    $this->usuarioRepository->save($validateResult->getEntity());
                    $validateResult = $usuarioFactory->save($params, $authorization);
                    if (! $validateResult->hasError()) {
                        $response = $validateResult->getEntity();
                        $usuario->setUserId($response['additional']['id']);
                        $this->usuarioRepository->flush();
                        $this->usuarioRepository->commit();
                    } else {
                        $this->usuarioRepository->rollback();
                    }
                } catch (Exception $exception) {
                    $this->usuarioRepository->rollback();
                    $validateResult = new ValidateResultado(null, [$exception->getMessage()]);
                }
            }
        }
        return $this->processResult(
            $validateResult,
            function () use ($success, $usuario) {
                return call_user_func($success, $usuario);
            },
            $error
        );
    }

    /**
     * Listado de todos los usuarios
     *
     * @param integer $limit Cantidad de resultados
     * @param integer $offset Inicio de la búsqueda}
     * @param integer $params 
     * @return object
     */
    public function findAllPaginate($limit, $offset, $authorization, $onError, $params = [])
    {
        $validateResultado = $this->rolesServices->getUsuario($authorization);
        $result = [];
        $resultset = [];

        if (! $validateResultado->hasError()) {

            $usuList = new UsuarioListado($this->usuarioIntegration);
            $result = $usuList->getListadoUsuarios($authorization, $offset, $limit, $params);
            $resultset = [
                'resultset' => [
                    'count' => $usuList->getTotalListadoUsuarios($authorization, $params),
                    'offset' => $offset,
                    'limit' => $limit
                ]
            ];
        }

        return $this->processError(
            $validateResultado,
            function () use ($result, $resultset) {
                return $this->respuestaData($resultset, $result);
            },
            $onError
        );
    }
}
