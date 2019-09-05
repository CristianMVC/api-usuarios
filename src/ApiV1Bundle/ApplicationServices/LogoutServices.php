<?php
namespace ApiV1Bundle\ApplicationServices;

use Symfony\Component\DependencyInjection\Container;
use ApiV1Bundle\ExternalServices\SecurityIntegration;
use ApiV1Bundle\Repository\TokenRepository;
use ApiV1Bundle\Entity\Factory\TokenFactory;
use ApiV1Bundle\Helper\ServicesHelper;

class LogoutServices extends SNTUserServices
{
    /**
     * Integration service
     */
    private $integrationService;

    /**
     * Token repository
     */
    private $tokenRepository;

    /**
     * Logout service constructor
     *
     * @param Container $container
     * @param SecurityIntegration $integrationService
     */
    public function __construct(
        Container $container,
        SecurityIntegration $integrationService,
        TokenRepository $tokenRepository
    ) {
        parent::__construct($container);
        $this->integrationService = $integrationService;
        $this->tokenRepository = $tokenRepository;
    }

    /**
     * Logout user
     * @param $token
     * @param $onSuccess
     * @param $onError
     */
    public function logout($token, $onSuccess)
    {
        $token = ServicesHelper::cleanToken($token);
        // set token as invalid on SNT
        $SNTresponse = $this->integrationService->logout('snt', $token);
        // set token as invalid on SNC
        $SNCresponse = $this->integrationService->logout('snc', $token);
        // chequeamos que no haya habido errores
        if ($SNTresponse->hasError()) {
            return $SNTresponse;
        }
        if ($SNCresponse->hasError()) {
            return $SNCresponse;
        }
        // invalidamos el token en la API de usuarios
        $invalidToken = new TokenFactory($this->tokenRepository);
        $validateResult = $invalidToken->insert(md5($token));
        if (! $validateResult->hasError()) {
            // check if the token exists first
            $tokenExists = $this->tokenRepository->findOneByToken(md5($token));
            if (! $tokenExists) {
                $this->tokenRepository->add($validateResult->getEntity());
            }
        }
        // call function
        return call_user_func($onSuccess, $this->tokenRepository->flush());
    }
}
