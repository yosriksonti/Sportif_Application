<?php

namespace App\Controller;

use App\Entity\Application;
use App\Form\ApplicationType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/application')]
class ApplicationController extends AbstractController
{
    #[Route('/', name: 'app_application_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $usr = $this->getUser();
        $applications = $entityManager
            ->getRepository(Application::class)
            ->findAll();
        $this->user = $usr;
        return $this->render('application/index.html.twig', [
            'applications' => $applications,
        ]);
    }

    #[Route('/new', name: 'app_application_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $usr = $this->getUser();
        $application = new Application();
        $form = $this->createForm(ApplicationType::class, $application);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $application->setCreatedAt(new \DateTime());
            $application->setUpdatedAt(new \DateTime());
            $entityManager->persist($application);
            $entityManager->flush();
            $this->user = $usr;
            return $this->redirectToRoute('app_application_show', ['id' => $application->getId()], Response::HTTP_SEE_OTHER);
        }
        $this->user = $usr;
        return $this->renderForm('application/new.html.twig', [
            'application' => $application,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_application_show', methods: ['GET'])]
    public function show(Application $application, EntityManagerInterface $entityManager): Response
    {
        $usr = $this->getUser();
        
        $this->user = $usr;
        return $this->render('application/show.html.twig', [
            'application' => $application,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_application_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Application $application, EntityManagerInterface $entityManager): Response
    {
        $usr = $this->getUser();
       
        $form = $this->createForm(ApplicationType::class, $application);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->user = $usr;
            return $this->redirectToRoute('app_application_show', ['id' => $application->getId()], Response::HTTP_SEE_OTHER);
        }
        $this->user = $usr;
        return $this->renderForm('application/edit.html.twig', [
            'application' => $application,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_application_delete', methods: ['POST'])]
    public function delete(Request $request, Application $application, EntityManagerInterface $entityManager): Response
    {
        $usr = $this->getUser();
        if ($this->isCsrfTokenValid('delete'.$application->getId(), $request->request->get('_token'))) {
            $entityManager->remove($application);
            $entityManager->flush();
        }
        $this->user = $usr;
        return $this->redirectToRoute('app_application_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/api/{secret}', name: 'app_application_api', methods: ['GET'])]
    public function api($secret ,Request $request, EntityManagerInterface $entityManager): Response
    {
        $usr = $this->getUser();
        $application = $entityManager
            ->getRepository(Application::class)
            ->findOneBy(['secret' => $secret]);
        $this->user = $usr;
        echo  $request->server->get('HTTP_HOST') == $application->getNom();
        $response = new Response(json_encode(array('active' => $application->getActive() && $request->server->get('HTTP_HOST') == $application->getNom())));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
    
}
