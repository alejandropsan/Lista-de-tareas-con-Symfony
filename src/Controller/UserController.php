<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;
use App\Form\RegisterType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserController extends AbstractController
{

    public function register(Request $request, UserPasswordHasherInterface $passwordHasher,
            ManagerRegistry $doctrine): Response
    {
        //crear formulario
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);
        
        //rellenar el objeto con los datos del formulario
        $form->handleRequest($request);
        
        //comprobar si el form se ha enviado
        if($form->isSubmitted() && $form->isValid()){
            //modificando el objeto para guardarlo
            $user->SetRole('ROLE_USER');
            $user->setCreatedAt(new \Datetime('now'));
            
            //cifrando la contraseña
            $passwordHasher = $passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($passwordHasher);
            
            //guardar el usuario
            $em = $doctrine->getManager();
            $em->persist($user);
            $em->flush();
            
            return $this->redirectToRoute('tasks');
        }
        
        return $this->render('user/register.html.twig', [
           'form' => $form->createView()
        ]);
    }
    
    public function login(AuthenticationUtils $autenticationUtils) {
        $error = $autenticationUtils->getLastAuthenticationError();
        
        $lastUsername = $autenticationUtils->getLastUsername();
        
        return $this->render('user/login.html.twig', array(
            'error' => $error,
            'last_username' => $lastUsername,
        ));
    }
}
