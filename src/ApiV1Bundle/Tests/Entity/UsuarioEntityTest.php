<?php
namespace ApiV1Bundle\Tests\Entity;

use ApiV1Bundle\Tests\Entity\EntityTestCase;
use ApiV1Bundle\Entity\Usuario;

class UsuarioEntityTest extends EntityTestCase
{
    /** @var \ApiV1Bundle\Repository\UsuarioRepository $usuarioRepository */
    private $usuarioRepository;

    public function setUp()
    {
        parent::setUp();
        $this->usuarioRepository = $this->em->getRepository('ApiV1Bundle:Usuario');
    }

    /**
     * Test CREATE
     * @return number
     */
    public function testCreate()
    {
        $user = new Usuario(
            'name',
            'lastname',
           'usernameTestCreate',
                '1',
            'snt',
                    rand(0,999));
        $this->em->persist($user);
        // test
        $this->assertEquals('usernameTestCreate', trim($user->getUsername()));
        // save
        $this->em->flush();
        // return
        return $user->getId();
    }

    /**
     * Test READ
     * param Integer $Id identificador único que corresponde al ID que devuelve testCreate
     * @depends testCreate
     */
    public function testRead($id)
    {
        $user = $this->usuarioRepository->find($id);

        // test
        $this->assertEquals('usernameTestCreate', trim($user->getUsername()));
        $this->assertEquals('snt', $user->getSistema());
        // return
        return $id;
    }

    /**
     * Test UPDATE
     * param Integer $Id identificador único que corresponde al ID que devuelve testRead
     * @depends testRead
     */
    public function testUpdate($id)
    {
        // update
        $user = $this->usuarioRepository->find($id);
        $user->setUsername('usernameTestUpdate');
        // save
        $this->em->flush();
        // recover again
        $user = $this->usuarioRepository->find($id);
        $this->assertEquals('usernameTestUpdate', trim($user->getUsername()));
        // return
        return $id;
    }

    /**
     * Test DELETE
     * param Integer $Id identificador único que corresponde al ID que devuelve testUpdate
     * @depends testUpdate
     */
    public function testDelete($id)
    {
        $user = $this->usuarioRepository->find($id);
        $this->em->remove($user);
        // save
        $this->em->flush();
        // recover again
        $user = $this->usuarioRepository->find($id);
        $this->assertNotEquals(null, $user->getFechaBorrado());
    }
}
