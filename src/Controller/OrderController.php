<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OrderController extends AbstractController
{
    #[Route('/commandes', name: 'orders')]
    public function index(): Response
    {
        if ($this->getUser()) 
        {
            $orders = $this->getUser()->getOrders();
        }

        return $this->render('order/index.html.twig', [
            'orders' => $orders,
        ]);
    }
}
