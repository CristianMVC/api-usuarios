<?php

namespace ApiV1Bundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ApiV1Bundle\ExternalServices\UsuarioIntegration;
use JMS\Serializer\Exception\Exception;

class SnuUsuariosMigrateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('snu:usuarios:migrate')
            ->setDescription('Ejecuta la migración de usuarios desde SNT y SNC a SNU')
            ->setHelp('Comando para migración de usuarios desde SNT y SNC a SNU. Requiere usuario y contraseña de administrador en SNT.')
            ->addArgument('username', InputArgument::OPTIONAL, 'Usuario admin de SNT')
            ->addArgument('password', InputArgument::OPTIONAL, 'Contraseña')
        ;
    }

    /**
     * Ejecuta la migración de usuarios desde SNT y SNC a SNU
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();
        $usuarioRepository = $em->getRepository('ApiV1Bundle:Usuario');
        $usuarioServices = $container->get('user.services.usuario');
        try {
            $username = $input->getArgument('username');
            $password = $input->getArgument('password');
            if ($username && $password){
                $loginServices = $container->get('user.services.login');
                    $login = $loginServices->loginSNU([
                        'username' => $username,
                        'password' => $password]);
                    if (isset($login['rol_id']) && $login['rol_id'] != 1) {
                        $output->writeln("El usuario debe tener permiso de Administrador.");
                        exit;
                    }
                    if (isset($login['token'])){

                        //Migrar Usuarios desde SNT
                        $output->writeln("Migrando usuarios desde SNT...");
                        $usuariosSNT = $usuarioServices->findAll('snt', ['Authorization' => 'Bearer ' . $login['token']]);
                        if (isset($usuariosSNT['result'])){
                            $conn = $em->getConnection();
                            foreach ($usuariosSNT['result'] as $usuarioSNT) {
                                if (!$usuarioRepository->findByUserIdRolId($usuarioSNT['id'], $usuarioSNT['rol'])) {
                                    $now = date("Y-m-d H:i:s");
                                    $data = [
                                        'sistema' => 'snt',
                                        'user_id' => $usuarioSNT['id'],
                                        'username' => $usuarioSNT['usuario'],
                                        'nombre' => $usuarioSNT['nombre'],
                                        'apellido' => $usuarioSNT['apellido'],
                                        'rol_id' => $usuarioSNT['rol'],
                                        'fecha_creado' => $now,
                                        'fecha_modificado' => $now
                                    ];
                                    if (isset($usuarioSNT['puntoAtencion']['id'])) {
                                        $other = array( 'punto_atencion_id' => $usuarioSNT['puntoAtencion']['id'] );
                                        $data = array_merge( $data, $other);
                                    }
                                    if (isset($usuarioSNT['organismo']['id'])) {
                                        $other = array( 'organismo_id' => $usuarioSNT['organismo']['id'] );
                                        $data = array_merge( $data, $other);
                                    }
                                    if (isset($usuarioSNT['area']['id'])) {
                                        $other = array( 'area_id' => $usuarioSNT['area']['id'] );
                                        $data = array_merge( $data, $other);
                                    }
                                    $conn->insert('usuario', $data);
                                }
                            }
                            $output->writeln("Se migraron los usuarios desde SNT");
                        }else {
                            if (isset($usuariosSNT['userMessage']['errors'])) {
                                foreach ($usuariosSNT['userMessage']['errors'] as $error) {
                                    $output->writeln($error);
                                }
                            } else {
                                $output->writeln("No se pudo obtener usuarios desde SNT");
                            }
                        }

                        //Migrar Usuarios Agentes(rol 5) de SNC
                        $output->writeln("Migrando usuarios desde SNC...");
                        $usuariosSNC = $usuarioServices->findAll('snc', ['Authorization' => 'Bearer ' . $login['token']]);
                        if (isset($usuariosSNC['result'])){
                            $conn = $em->getConnection();
                            foreach ($usuariosSNC['result'] as $usuarioSNC) {
                                if ($usuarioSNC['rol']==5) {
                                    if (!$usuarioRepository->findByUserIdRolId($usuarioSNC['id'], $usuarioSNC['rol'])) {
                                        $now = date("Y-m-d H:i:s");
                                        $data = [
                                            'sistema' => 'snc',
                                            'user_id' => $usuarioSNC['id'],
                                            'username' => $usuarioSNC['username'],
                                            'nombre' => $usuarioSNC['nombre'],
                                            'apellido' => $usuarioSNC['apellido'],
                                            'rol_id' => 5,
                                            'fecha_creado' => $now,
                                            'fecha_modificado' => $now
                                        ];
                                        if (isset($usuarioSNC['puntoAtencion']['id'])) {
                                            $other = array( 'punto_atencion_id' => $usuarioSNC['puntoAtencion']['id'] );
                                            $data = array_merge( $data, $other);
                                        }
                                        $conn->insert('usuario', $data);
                                    }
                                }
                            }
                            $output->writeln("Se migraron los usuarios desde SNC");
                        }else {
                            if (isset($usuariosSNC['userMessage']['errors'])) {
                                foreach ($usuariosSNC['userMessage']['errors'] as $error) {
                                    $output->writeln($error);
                                }
                            } else {
                                $output->writeln("No se pudo obtener usuarios desde SNC");
                            }
                        }

                    }else{
                        if (isset($login['errors'])) {
                            foreach ($login['errors'] as $error) {
                                $output->writeln($error);
                            }
                        } else {
                            $output->writeln("Ocurrió un error durante el login");
                        }
                    }
            }else{
                $output->writeln("No se especifico usuario y/o contraseña");
            }
        } catch (Exception $exception) {
            $output->writeln("Ocurrió un error durante la migracion.");
        }
    }
}
