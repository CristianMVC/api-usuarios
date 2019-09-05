<?php
namespace ApiV1Bundle\Helper;

class ServicesHelper
{
    /**
     * Convertir en array
     *
     * @param $data
     * @return mixed|NULL
     */
    public static function toArray($data)
    {
        if (is_array($data)) {
            return $data;
        }
        if (json_decode($data)) {
            return json_decode($data, true);
        }
        return null;
    }

    /**
     * Check if is array
     *
     * @param mixed $data
     * @return boolean
     */
    public static function isArray($data)
    {
        if (is_array($data)) {
            return true;
        }
        if (json_decode($data)) {
            return true;
        }
        return false;
    }

    /**
     * Generar contraseña al azar
     *
     * @param number $len
     * @return string
     */
    public static function randomPassword($len = 8)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = [];
        $alphaLength = strlen($chars) - 1;
        for ($i = 0; $i < $len; $i ++) {
            $n = rand(0, $alphaLength);
            $pass[] = $chars[$n];
        }
        return implode($pass);
    }

    /**
     * Clean token
     * @param $token
     * @return string
     */
    public static function cleanToken($token)
    {
        if (substr($token, 0, strlen('Bearer')) === 'Bearer')
        {
            $parts = explode(' ', $token);
            $token = $parts[1];
        }
        return $token;
    }
}
