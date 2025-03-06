<?php

namespace App\Controller;

use App\Entity\Offer;
use App\Entity\Order;
use App\Repository\OfferRepository;
use App\Repository\SettingRepository;
use App\Repository\UnitRepository;
use Doctrine\ORM\EntityManagerInterface;
use GetterTool;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OfferController extends AbstractController
{
    #[Route('/offres', name: 'offers')]
    public function index(OfferRepository $offerRepository, SettingRepository $settingRepository): Response
    {
        $offers = $offerRepository->findBy(array("available" => true));
        $unitPrice = $settingRepository->findOneBy(["settingKey" => "currentUnitPrice"]);
        //$mostPopulars = $offerRepository->findMostPopular();

        return $this->render('offer/index.html.twig', [
            'offers' => $offers,
            'unitPrice' => intval($unitPrice->getValue(), 10),
            //'mostPopulars' => $mostPopulars,
        ]);
    }

    #[Route('/offres/{id}', name: 'offer', methods: ['GET'])]
    public function offer(
        Offer $offer, 
        UnitRepository $unitRepository, 
        SettingRepository $settingRepository, 
    ): Response {

        $unitPrice = GetterTool::GetCurrentPrice($settingRepository);

        // Récupération des unités disponibles
        $availableUnits = $unitRepository->findAvailableUnits();
        $nbrUnits = count($availableUnits);

        return $this->render('offer/offer.html.twig', [
            'offer' => $offer,
            'unitPrice' => $unitPrice,
            'isCurrentlyAvailable' => $offer->getUnitLimit() <= $nbrUnits,
        ]);
    }

    #[Route('/offres/{id}', name: 'offer_POST', methods: ['POST'])]
    public function offer_POST(
        Offer $offer,
        UnitRepository $unitRepository, 
        SettingRepository $settingRepository, 
        EntityManagerInterface $entityManager
    ): Response {

        $unitPrice = GetterTool::GetCurrentPrice($settingRepository);

        // Récupération des unités disponibles
        $availableUnits = $unitRepository->findAvailableUnits();
        $nbrUnits = count($availableUnits);

        // Vérification si l'utilisateur est connecté
        if (!$this->getUser()) {
            return $this->redirectToRoute('login');
        }

        // Vérification si suffisamment d'unités sont disponibles
        if ($offer->getUnitLimit() > $nbrUnits) {
            $this->addFlash('danger', 'Stock insuffisant pour passer cette commande.');
            return $this->redirectToRoute('offer', ['id' => $offer->getId()]);
        }

        // Création d'une nouvelle commande
        $order = new Order();
        $order->setCustomer($this->getUser());
        $order->setOffer($offer);
        $order->setStartDate(new \DateTime());
        $order->setEndDate(null);
        $order->setUnitPrice($unitPrice);

        $entityManager->persist($order);

        // Affectation des unités à la commande
        for ($i = 0; $i < $offer->getUnitLimit(); $i++) { 
            $unit = $availableUnits[$i];
            $unit->addOrder($order);
            $entityManager->persist($unit);
        }

        // Sauvegarde dans la base de données
        $entityManager->flush();

        // Redirection vers la page de confirmation
        return $this->redirectToRoute('order_success');
    }

    #[Route('/success', name: 'order_success')]
    public function orderSuccess(): Response
    {
        return $this->render('offer/success.html.twig');
    }
}
