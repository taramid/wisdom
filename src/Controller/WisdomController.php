<?php

namespace App\Controller;

use App\Contract\RandomWisdom\PickerInterface;
use App\Entity\Wisdom;
use App\Form\WisdomType;
use App\Repository\WisdomRepository;
use App\Service\RandomWisdom\RedisPicker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/wisdom')]
final class WisdomController extends AbstractController
{
    #[Route(name: 'app_wisdom_index', methods: ['GET'])]
    public function index(WisdomRepository $wisdomRepository): Response
    {
        return $this->render('wisdom/index.html.twig', [
            'wisdoms' => $wisdomRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_wisdom_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $wisdom = new Wisdom();
        $form = $this->createForm(WisdomType::class, $wisdom);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($wisdom);
            $entityManager->flush();

            return $this->redirectToRoute('app_wisdom_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('wisdom/new.html.twig', [
            'wisdom' => $wisdom,
            'form' => $form,
        ]);
    }

    #[Route('/random', name: 'app_wisdom_random')]
    public function random(WisdomRepository $wisdomRepository, PickerInterface $wisdomPicker): Response
    {
        $randomId = $wisdomPicker->getRandomId();

        return $this->render('wisdom/random.html.twig', [
            'wisdom' => $randomId ? $wisdomRepository->find($randomId) : null,
        ]);
    }

    #[Route('/{id}', name: 'app_wisdom_show', methods: ['GET'])]
    public function show(Wisdom $wisdom): Response
    {
        return $this->render('wisdom/show.html.twig', [
            'wisdom' => $wisdom,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_wisdom_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Wisdom $wisdom, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(WisdomType::class, $wisdom);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_wisdom_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('wisdom/edit.html.twig', [
            'wisdom' => $wisdom,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_wisdom_delete', methods: ['POST'])]
    public function delete(Request $request, Wisdom $wisdom, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$wisdom->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($wisdom);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_wisdom_index', [], Response::HTTP_SEE_OTHER);
    }
}
