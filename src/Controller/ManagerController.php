<?php

namespace App\Controller;

use App\Entity\Manager;
use App\Form\ManagerType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


#[Route('/manager')]
class ManagerController extends AbstractController
{
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }
    #[Route('/', name: 'app_manager_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $usr = $this->getUser();
        $managers = $entityManager
            ->getRepository(Manager::class)
            ->findAll();
        $this->user = $usr;
        return $this->render('manager/index.html.twig', [
            'managers' => $managers,
        ]);
    }

    #[Route('/new', name: 'app_manager_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $usr = $this->getUser();
        $manager = new Manager();
        $form = $this->createForm(ManagerType::class, $manager);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $manager->setCreatedAt(new \DateTime());
            $manager->setUpdatedAt(new \DateTime());
            $manager->setPassword($this->passwordHasher->hashPassword(
                $manager,
                $form->get('password')->getData()
            ));
            $entityManager->persist($manager);
            $entityManager->flush();

            $this->user = $usr;
        return $this->redirectToRoute('app_manager_index', [], Response::HTTP_SEE_OTHER);
        }

        $this->user = $usr;
        return $this->renderForm('manager/new.html.twig', [
            'manager' => $manager,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_manager_show', methods: ['GET'])]
    public function show(Manager $manager): Response
    {
        $usr = $this->getUser();
        
        $this->user = $usr;
        return $this->render('manager/show.html.twig', [
            'manager' => $manager,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_manager_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Manager $manager, EntityManagerInterface $entityManager): Response
    {
        $usr = $this->getUser();
        
        $form = $this->createForm(ManagerType::class, $manager);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if($form->get('password')->getData()!=null) {
                $manager->setPassword($this->passwordHasher->hashPassword(
                    $manager,
                    $form->get('password')->getData()
                ));
            }
            $entityManager->flush();

            $this->user = $usr;
        return $this->redirectToRoute('app_manager_index', [], Response::HTTP_SEE_OTHER);
        }

        $this->user = $usr;
        return $this->renderForm('manager/edit.html.twig', [
            'manager' => $manager,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_manager_delete', methods: ['POST'])]
    public function delete(Request $request, Manager $manager, EntityManagerInterface $entityManager): Response
    {
        $usr = $this->getUser();
        if ($this->isCsrfTokenValid('delete'.$manager->getId(), $request->request->get('_token'))) {
            $entityManager->remove($manager);
            $entityManager->flush();
        }

        $this->user = $usr;
        return $this->redirectToRoute('app_manager_index', [], Response::HTTP_SEE_OTHER);
    }
}
