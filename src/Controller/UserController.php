<?php

namespace App\Controller;
use Dompdf\Options;
use App\Entity\Medecin;
use OpenAI;
use App\Form\DoctorType;
use App\Form\UserType;
use App\Repository\MedecinRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Dompdf\Dompdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{
    #[Route('/pagehome', name: 'pagehome')]
    public function index(): Response
    {
        return $this->render('user/pagehome.html.twig', [
            
        ]);
    }



    private $passwordEncoder;//hashage mdp

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }




    #[Route('/edituser/{id}', name: 'edituser')]
    public function editadmin($id, ManagerRegistry $managerRegistry, Request $req, MedecinRepository $UserRepository, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $entityManager = $managerRegistry->getManager();
        $detaid = $UserRepository->find($id);
    
        if (!$detaid) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }
    
        $form = $this->createForm(UserType::class, $detaid);
        $form->handleRequest($req);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $detaid->setSpecialite(0);
            $detaid->setAdress(0);
            // Hacher le mot de passe
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
                $detaid->setPhoto($newFilename);
            }
            $detaid = $form->getData();
            $hashedPassword = $passwordEncoder->encodePassword($detaid, $detaid->getPassword());
            $detaid->setPassword($hashedPassword);
    
            $entityManager->persist($detaid);
            $entityManager->flush();
    
            return $this->redirectToRoute('app_login');
        }
    
        return $this->render('user/edituser.html.twig', [
            'form' => $form->createView()
        ]);
    }
    
    
    
    



    
    
    
    #[Route('/editdoc/{id}', name: 'editdoc')]
    public function editdoc($id, ManagerRegistry $managerRegistry, Request $req, MedecinRepository $UserRepository, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $entityManager = $managerRegistry->getManager();
        $detaid = $UserRepository->find($id);
    
        if (!$detaid) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }
    
        $form = $this->createForm(DoctorType::class, $detaid);
        $form->handleRequest($req);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Hacher le mot de passe
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
                $detaid->setPhoto($newFilename);
            }
            $detaid = $form->getData();
            $hashedPassword = $passwordEncoder->encodePassword($detaid, $detaid->getPassword());
            $detaid->setPassword($hashedPassword);
    
            $entityManager->persist($detaid);
            $entityManager->flush();
    
            return $this->redirectToRoute('app_login');
        }
    
        return $this->render('user/editdoc.html.twig', [
            'form' => $form->createView()
        ]);
    }
    
    
    
    
    
    
        #[Route('/profile', name: 'profile')]
        public function profile(SessionInterface $session): Response
        {
            // Retrieve user data from the session
            $user = $session->get('user');
    
            // Render the profile page template with user data
            return $this->render('user/profile.html.twig', [
                'user' => $user,
            ]);
        }
    
    
        #[Route('/profiledoc', name: 'profiledoc')]
        public function profiledoc(SessionInterface $session): Response
        {
            // Retrieve user data from the session
            $user = $session->get('user');
    
            // Render the profile page template with user data
            return $this->render('user/profiledoc.html.twig', [
                'user' => $user,
            ]);
        }
    
    
    
    
        #[Route('/showdbuser', name: 'showdbuser')]
        public function showdbuser(MedecinRepository $userRepository): Response
        {
           
            // Fetch user data from the repository
            $users = $userRepository->findAll();
           
            // Pass PDF content and user data to the view
            return $this->render('user/showdbuser.html.twig', [
                'user' => $users,
            ]);
        }

        #[Route('/pdfadmin', name: 'pdfadmin')]
        public function generatePdf(MedecinRepository $userRepository): Response
{
    $dompdf = new Dompdf();

    // Fetch user data from the repository
    $users = $userRepository->findAll();

    // Render HTML content for pdfadmin.html.twig
    $html = $this->renderView('user/pdfadmin.html.twig', [
        'user' => $users,
    ]);

    // Load HTML to Dompdf
    $dompdf->loadHtml($html);

    // Set paper size and orientation
    $dompdf->setPaper('A4', 'portrait');

    // Create options instance
    $options = new Options();

    // Set options
    $options->set('dpi', 150); // Adjust DPI for better image and text quality
    $options->set('isHtml5ParserEnabled', true); // Enable HTML5 parser
    // Add more options as needed

    // Apply options to Dompdf
    $dompdf->setOptions($options);

    // Render PDF
    $dompdf->render();

    // Get PDF content
    $pdfOutput = $dompdf->output();

    // Return PDF response
    return new Response($pdfOutput, Response::HTTP_OK, [
        'Content-Type' => 'application/pdf',
        'pdfContent' => base64_encode($pdfOutput),
    ]);
}
    
        #[Route('/showdocuser', name: 'showdocuser')] //affichage
        public function showdocuser(MedecinRepository $userRepository): Response
        {
    
            $user=$userRepository->findAll();
            return $this->render('user/showdocuser.html.twig', [
                'user'=>$user,
    
            ]);
        }
    
        #[Route('/showdoc', name: 'showdoc')] //affichage
        public function showdoc(MedecinRepository $userRepository): Response
        {
    
            $user=$userRepository->findAll();
            return $this->render('user/showdoc.html.twig', [
                'user'=>$user,
    
            ]);
        }
    
    
    
        #[Route('/deleteuser/{id}', name: 'deleteuser')]
        public function deleteUser($id, SessionInterface $session): Response
        {
            // The $id parameter is automatically resolved from the route parameters
            
            $userRepository = $this->getDoctrine()->getRepository(Medecin::class);
    
            // Find the user by ID
            $user = $userRepository->find($id);
    
            // Check if the user exists
            if (!$user) {
                throw $this->createNotFoundException('User not founddddd');
            }
    
            // Delete the user
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
    
            // Clear the session
            $session->invalidate();
    
            // Redirect to an appropriate page after deletion
            // For example, redirect to the user list page
            return $this->redirectToRoute('signup');
        }


        #[Route('/deleteuserad/{id}', name: 'deleteuserad')]
        public function admindeleteuser($id): Response
        {
            // Retrieve the user entity by ID
            $userRepository = $this->getDoctrine()->getRepository(Medecin::class);
            $user = $userRepository->find($id);

            if (!$user) {
                throw $this->createNotFoundException('User not found');
            }
    
            // Remove the user entity
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
    
            // Add a flash message to notify the admin
            $this->addFlash('success', 'User deleted successfully');
    
            // Redirect to the page displaying the list of users
            return $this->redirectToRoute('showdbuser');
        }
        

        #[Route('/switchroleadmin/{id}', name: 'switchroleadmin')]
        public function switchUserRoleToAdmin($id, MedecinRepository $userRepository): Response
        {
            $entityManager = $this->getDoctrine()->getManager();
            
            // Retrieve the user entity by ID
            $user = $userRepository->find($id);
            
            // Check if the user exists
            if (!$user) {
                throw $this->createNotFoundException('User not found');
            }
        
            // Set the user's role to "admin"
            $user->setRoles(['admin']);
            
            // Persist changes to the database
            $entityManager->persist($user);
            $entityManager->flush();
        
            // Add a flash message to notify the admin
            $this->addFlash('success', 'User role switched to admin successfully');
            
            // Redirect to an appropriate page after switching the user's role
            return $this->redirectToRoute('showdbuser');
        }
       



        #[Route('/switchroleutoser/{id}', name: 'switchroleutoser')]
        public function switchUserRoleToUser($id, MedecinRepository $userRepository): Response
        {
            $entityManager = $this->getDoctrine()->getManager();
            
            // Retrieve the user entity by ID
            $user = $userRepository->find($id);
            
            // Check if the user exists
            if (!$user) {
                throw $this->createNotFoundException('User not found');
            }
        
            // Set the user's role to "admin"
            $user->setRoles(['user']);
            
            // Persist changes to the database
            $entityManager->persist($user);
            $entityManager->flush();
        
            // Add a flash message to notify the admin
            $this->addFlash('success', 'User role switched to admin successfully');
            
            // Redirect to an appropriate page after switching the user's role
            return $this->redirectToRoute('showdbuser');
        }
       


        
        #[Route('/addadmin', name: 'addadmin')]
        public function addformuser( Request $req, SessionInterface $session): Response
        {
            $user = new Medecin();
    
            $form = $this->createForm(UserType::class, $user);
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
             $user->setRoles(['ROLE_ADMIN']);
              $em=$this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();
                
                return $this->redirectToRoute('showdbuser');
            }
            return $this->renderForm('user/addadmin.html.twig', [
                'form' => $form
            ]);
        }
        #[Route('/editadmin/{id}', name: 'editadmin')]
        public function editad($id, ManagerRegistry $managerRegistry, Request $req, MedecinRepository $UserRepository, UserPasswordEncoderInterface $passwordEncoder): Response
        {
            $entityManager = $managerRegistry->getManager();
            $detaid = $UserRepository->find($id);
        
            if (!$detaid) {
                throw $this->createNotFoundException('Utilisateur non trouvé');
            }
        
            $form = $this->createForm(UserType::class, $detaid);
            $form->handleRequest($req);
        
            if ($form->isSubmitted() && $form->isValid()) {
                $detaid->setSpecialite(0);
                $detaid->setAdress(0);
                // Hacher le mot de passe
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
                    $detaid->setPhoto($newFilename);
                }
                $detaid = $form->getData();
                $hashedPassword = $passwordEncoder->encodePassword($detaid, $detaid->getPassword());
                $detaid->setPassword($hashedPassword);
        
                $entityManager->persist($detaid);
                $entityManager->flush();
        
                return $this->redirectToRoute('app_login');
            }
        
            return $this->render('user/editadmin.html.twig', [
                'form' => $form->createView()
            ]);
        }
    
    
    
    
        #[Route('/adminprofile', name: 'adminprofile')]
        public function adminprofile(SessionInterface $session): Response
        {
            // Retrieve user data from the session
            $user = $session->get('user');
    
            // Render the profile page template with user data
            return $this->render('user/adminprofile.html.twig', [
                'user' => $user,
            ]);
        }

        #[Route('/block_user/{id}', name: 'block_user')]
        public function blockUser($id, EntityManagerInterface $entityManager, MedecinRepository $userRepository): Response
        {
            // Retrieve the user entity by ID
            $user = $userRepository->find($id);
            
            // Check if the user exists
            if (!$user) {
                throw $this->createNotFoundException('User not found');
            }
    
            // Check if the user is already blocked
            if ($user->getToken() == 0) {
                $this->addFlash('warning', 'User is already blocked');
                return $this->redirectToRoute('user_list'); // Redirect to the user list page, adjust the route name as per your application
            }
    
            // Block the user by setting the token to 0
            $user->setToken(0);
    
            // Persist changes to the database
            $entityManager->flush();
    
            // Add a flash message to notify the admin
            $this->addFlash('success', 'User blocked successfully');
    
            // Redirect to an appropriate page
            return $this->redirectToRoute('showdbuser'); // Redirect to the user list page, adjust the route name as per your application
        }
    

        #[Route('/unblock_user/{id}', name: 'unblock_user')]
        public function unblockUser($id, EntityManagerInterface $entityManager, MedecinRepository $userRepository): Response
        {
            // Retrieve the user entity by ID
            $user = $userRepository->find($id);
            
            // Check if the user exists
            if (!$user) {
                throw $this->createNotFoundException('User not found');
            }
    
            // Check if the user is already blocked
            if ($user->getToken() == 1) {
                $this->addFlash('warning', 'User is already unblocked');
                return $this->redirectToRoute('user_list'); // Redirect to the user list page, adjust the route name as per your application
            }
    
            // Block the user by setting the token to 0
            $user->setToken(1);
    
            // Persist changes to the database
            $entityManager->flush();
    
            // Add a flash message to notify the admin
            $this->addFlash('success', 'User unblocked successfully');
    
            // Redirect to an appropriate page
            return $this->redirectToRoute('showdbuser'); // Redirect to the user list page, adjust the route name as per your application
        }
    
   
        #[Route('/docchart', name: 'docchart')]
        public function specialtiesChart(MedecinRepository $doctorRepository, EntityManagerInterface $entityManager): Response
        {
            $specialtiesData = $entityManager->getRepository(Medecin::class)->findAll();

    // Count occurrences of each specialty
    $specialties = [];
    foreach ($specialtiesData as $doctor) {
        $specialty = $doctor->getSpecialite();
        if ($specialty !== null && $specialty !== '0') {
            $specialties[] = $specialty;
        }
    }

    // Count the occurrences of each specialty
    $specialtyCounts = array_count_values($specialties);
    // Prepare data for chart
    $specialtyLabels = array_keys($specialtyCounts);
    $specialtyValues = array_values($specialtyCounts);


    // Render the chart template with data
    return $this->render('user/docchart.html.twig', [
        'specialtyLabels' => json_encode($specialtyLabels),
        'specialtyValues' => json_encode($specialtyValues),
    ]);
        }



        #[Route('/chatbotadmin', name: 'chatbotadmin', methods: ['GET', 'POST'])]
    public function chat(Request $request): Response
    {
       // Initialize variables to hold the question and response
    $question = '';
    $response = '';

    // Check if the form is submitted
    if ($request->isMethod('POST')) {
        // Retrieve the question from the form data
        $question = $request->request->get('text');

        // Check if the question is empty or null
        $myApiKey = $_ENV['OPENAI_KEY'];
    
    
        $client = OpenAI::client($myApiKey);

        $result = $client->completions()->create([
            'model' => 'gpt-3.5-turbo-instruct',
            'prompt' => $question,
            'max_tokens'=>2048
        ]);

        $response=$result->choices[0]->text;
    }

    // Render the chatbot template with the question and response
    return $this->render('user/chatbotadmin.html.twig', [
        'question' => $question,
        'response' => $response
    ]);
}

#[Route('/chatbotuser', name: 'chatbotuser', methods: ['GET', 'POST'])]
public function chatuser(Request $request): Response
{
   // Initialize variables to hold the question and response
$question = '';
$response = '';

// Check if the form is submitted
if ($request->isMethod('POST')) {
    // Retrieve the question from the form data
    $question = $request->request->get('text');

    // Check if the question is empty or null
    $myApiKey = $_ENV['OPENAI_KEY'];


    $client = OpenAI::client($myApiKey);

    $result = $client->completions()->create([
        'model' => 'gpt-3.5-turbo-instruct',
        'prompt' => $question,
        'max_tokens'=>2048
    ]);

    $response=$result->choices[0]->text;
}

// Render the chatbot template with the question and response
return $this->render('user/chatbotuser.html.twig', [
    'question' => $question,
    'response' => $response
]);
}

}

