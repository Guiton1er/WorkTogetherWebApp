<?php

namespace App\Controller;

use App\Repository\OfferRepository;
use App\Repository\SettingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OfferController extends AbstractController
{
    #[Route('/offres', name: 'offers')]
    public function index(OfferRepository $offerRepository, SettingRepository $settingRepository): Response
    {
        $offers = $offerRepository->findBy(array("available" => true));
        $unitPrice = $settingRepository->findOneBy(["settingKey" => "currentUnitPrice"]);
        $mostPopulars = $offerRepository->findMostPopular();

        return $this->render('offer/index.html.twig', [
            'offers' => $offers,
            'unitPrice' => intval($unitPrice->getValue(), 10),
            'mostPopulars' => $mostPopulars,
        ]);
    }
}
