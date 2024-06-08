<?php

namespace App\Controller;

use App\Entity\Medecin;
use App\Form\DoctorType;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationController extends AbstractController
{
    #[Route('/registration', name: 'app_registration')]
    public function index(): Response
    {
        return $this->render('registration/index.html.twig', [
            'controller_name' => 'RegistrationController',
        ]);
    }


    private $passwordEncoder;//hashage mdp

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    #[Route('/signup', name: 'signup')]
    public function addformuser( Request $req, SessionInterface $session): Response
    {
        $user = new Medecin();

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($req);

        if ($form->isSubmitted() and $form->isValid()) {
            $user->setSpecialite(0);
        $user->setAdress(0);
             $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPassword()));
              $photoFile = $form->get('photo')->getData();

            if ($photoFile) {
                $newFilename = uniqid().'.'.$photoFile->guessExtension();

                try {
                    $photoFile->move(
                        $this->getParameter('photo_directory'), // Specify the directory where photos should be uploaded
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Handle file upload error
                }

                // Update the photo path in the user entity
                $user->setPhoto($newFilename);
               
            }
            $session->set('user', $user);
         $user->setRoles(['user']);
          $em=$this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            
            return $this->redirectToRoute('app_login');
        }
        return $this->renderForm('registration/signup.html.twig', [
            'form' => $form
        ]);
    }


    
    #[Route('/signupdoc', name: 'signupdoc')]
    public function addformdoc( Request $req, SessionInterface $session): Response
    {
        $user = new Medecin();

        $form = $this->createForm(DoctorType::class, $user);
        $form->handleRequest($req);

        if ($form->isSubmitted() and $form->isValid()) {
             $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPassword()));
              $photoFile = $form->get('photo')->getData();

            if ($photoFile) {
                $newFilename = uniqid().'.'.$photoFile->guessExtension();

                try {
                    $photoFile->move(
                        $this->getParameter('photo_directory'), // Specify the directory where photos should be uploaded
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Handle file upload error
                }

                // Update the photo path in the user entity
                $user->setPhoto($newFilename);
               
            }
            $session->set('user', $user);
         $user->setRoles(['medecin']);
          $em=$this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            
            return $this->redirectToRoute('app_login');
        }
        return $this->renderForm('registration/signupdoc.html.twig', [
            'form' => $form
        ]);
    }




}
