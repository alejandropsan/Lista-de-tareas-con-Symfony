<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Task;
use App\Entity\User;
use App\Form\TaskType;
use Symfony\Component\Security\Core\User\UserInterface;

class TaskController extends AbstractController
{
    
    public function index(ManagerRegistry $doctrine)
    {
        
        //Prueba de entidades y relaciones
        $em = $doctrine->getManager();
        $task_repo = $doctrine->getRepository(Task::class);
        $tasks = $task_repo->findBy([], ['id' => 'DESC']);
        
       /*  
        $user_repo = $doctrine->getManager()->getRepository(User::class);
        $users = $user_repo->findAll();
        
        foreach($users as $user){
            echo "<h1>{$user->getName()} {$user->getSurname()}</h1>";
            
            foreach ($user->getTasks() as $task) {
                echo $task->getTitle()."<br/>";
            }
        }
        */
        
        
        return $this->render('task/index.html.twig', [
            'tasks' => $tasks
        ]);
    }
    
    public function detail(Task $task) {
        if(!$task){
            return $this->redirectToRoute('tasks');
        }
        return $this->render('task/detail.html.twig', [
            'task' => $task
        ]);
    }
    
    public function creation(Request $request, UserInterface $user, ManagerRegistry $doctrine) {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);
        
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()){
            $task->setCreatedAt(new \DateTime('now'));
            $task->setUser($user);
            
            $em = $doctrine->getManager();
            $em->persist($task);
            $em->flush();
            
            return $this->redirect(
                    $this->generateUrl('task_detail', ['id' => $task->getId()]));
            
        }
        
        return $this->render('task/creation.html.twig', [
            'form' =>$form->createView()
        ]);
    }
    
    public function myTasks(UserInterface $user) {
        $tasks = $user->getTasks();
        
        return $this->render('task/my-tasks.html.twig', [
            'tasks' => $tasks
        ]);
    }
    
    public function edit(Request $request, Task $task, UserInterface $user, ManagerRegistry $doctrine) {
        if(!$user || $user->getId() != $task->getUser()->getId()){
            return $this->redirectToRoute('tasks');
        }
        
        $form = $this->createForm(TaskType::class, $task);
        
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()){
            //$task->setCreatedAt(new \DateTime('now'));
            //$task->setUser($user);
            
            $em = $doctrine->getManager();
            $em->persist($task);
            $em->flush();
            
            return $this->redirect(
                    $this->generateUrl('task_detail', ['id' => $task->getId()]));
            
        }
        
        return $this->render('task/creation.html.twig', [
            'edit' => true,
            'form' => $form->createView()
        ]);
    }
    
    public function delete(Task $task, ManagerRegistry $doctrine, UserInterface $user) {
        if(!$user || $user->getId() != $task->getUser()->getId()){
            return $this->redirectToRoute('tasks');
        }
        
        if(!$task){
            return $this->redirectToRoute('tasks');
        }
        
        $em = $doctrine->getManager();
        $em->remove($task);
        $em->flush();
        
        return $this->redirectToRoute('tasks');
    }
}
