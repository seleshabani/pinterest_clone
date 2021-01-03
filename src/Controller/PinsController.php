<?php

namespace App\Controller;

use App\Entity\Pin;
use App\Entity\User;
use App\Form\PinType;
use App\Repository\PinRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PinsController extends AbstractController
{
    /**
     * @Route("/", name="app_home")
     */
    public function index(PinRepository $pinRepo): Response
    {
        $pins = $pinRepo->findBy([],['createdAt'=>'DESC']);

        return $this->render('pins/index.html.twig', [
            'pins' => $pins,
        ]);
    }
    /**
     * @Route("/pins/show/{id<[0-9]+>}", name="app_pins_show")
     */
    public function show(Pin $pin)
    {
        return $this->render('pins/show.html.twig',compact('pin'));
    }
    /**
     * @Route("/pins/create", name="app_pins_create")
     */
    public function create(Request $request, EntityManagerInterface $em)
    {
        $pin = new Pin;
        $form = $this->createForm(PinType::class,$pin);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $pin->setUser($this->getUser()); 
            $em->persist($pin);
            $em->flush();
            $this->addFlash('success','Pin créé avec succée');
            return $this->redirectToRoute('app_home');
        }
        return $this->render('pins/create.html.twig',['form'=>$form->createView()]);
    }
    /**
     * @Route("/pins/edit/{id<[0-9]+>}", name="app_pins_edit")
     */
    public function edit(Request $request,Pin $pin,EntityManagerInterface $em)
    {
        $form = $this->createForm(PinType::class,$pin);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            
            $em->flush();
            $this->addFlash('success','Pin modifié avec succée');
            return $this->redirectToRoute('app_home');
        } 
        return $this->render('pins/edit.html.twig',['pin'=>$pin,'form'=>$form->createView()]);
    }
    /**
     * @Route("/pins/delete/{id<[0-9]+>}", name="app_pins_delete",methods={"GET","DELETE"})
     */
    public function delete(Request $request,Pin $pin,EntityManagerInterface $em)
    {
        $token = $request->request->get('csrf_token');

       if ($this->isCsrfTokenValid('pin_deletion_'.$pin->getId(),$token)) {
           $em->remove($pin);
           $em->flush();
           $this->addFlash('warning','Pin supprimé');
       }
       return $this->redirectToRoute('app_home');
    }
}
