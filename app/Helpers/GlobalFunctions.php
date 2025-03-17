<?php
namespace App\Helpers;

class GlobalFunctions{

    public static function generate_UIID($taille): ?string
    {

        $Caracteres = 'ABCDEFGHIJKLMOPQRSTUVXWYZ0123456789';
        $QuantidadeCaracteres = strlen($Caracteres);
        $QuantidadeCaracteres--;
        $Hash=NULL;

        for($x=1;$x<=$taille;$x++){

            $Posicao = rand(0,$QuantidadeCaracteres);

            $Hash .= substr($Caracteres,$Posicao,1);
        }
        return $Hash;
    }
}
