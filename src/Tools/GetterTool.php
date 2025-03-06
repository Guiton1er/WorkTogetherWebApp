<?php

namespace App\Tools;

use App\Repository\SettingRepository;

class GetterTool{

    public static function GetCurrentPrice(SettingRepository $settingRepository) {
        // Récupération du prix unitaire
        $unitPriceSetting = $settingRepository->findOneBy(["settingKey" => "currentUnitPrice"]);
        if (!$unitPriceSetting) {
            throw new ErrorException("Le prix unitaire n'est pas défini dans les paramètres.");
        }
        return intval($unitPriceSetting->getValue(), 10);
    }
}