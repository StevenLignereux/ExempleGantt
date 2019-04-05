<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Gantt\UserGantt;
use App\Repository\UserRepository;
use Recode\DhtmlxBundle\Factory\GanttFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @var GanttFactory
     */
    private $ganttFactory;
    /**
     * @var UserRepository
     */
    private $repository;

    /**
     * UserController constructor.
     * @param GanttFactory $ganttFactory
     */
    public function __construct(GanttFactory $ganttFactory, UserRepository $repository)
    {
        $this->ganttFactory = $ganttFactory;
        $this->repository = $repository;
    }

    /**
     * @Route("/user", name="user_index", options={"expose" = true})
     * @param Request $request
     * @param UserRepository $repository
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function index(Request $request)
    {
        $gantt = $this->ganttFactory->create(UserGantt::class);
        $gantt->handleRequest($request);

        if ($gantt->isSubmitted()) {
            return $gantt->getResponse();
        }

        return $this->render('user/index.html.twig', [
            'gantt' => $gantt,
        ]);
    }



    /**
     * Create a new user
     *
     * @Route("/new", name="user_new", options={"expose" = true}, methods={"GET", "POST"})
     * @param Request $request
     *
     */
    public function newUser(Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user, [
            'action' => $this->generateUrl('user_new'),
            'method' => 'POST'
        ]);
        $form->handleRequest($request);



        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            return new JsonResponse([
                'type' => 'success'
            ]);
        }
        return $this->render('user/form.html.twig', array(
            'user' => $user,
            'form' => $form->createView(),
        ));
    }



    /**
     * Displays a form to existing user
     *
     * @Route("/{id}/edit", name="user_edit", options={"expose" = true}, methods={"GET", "POST"})
     * @param Request $request
     * @param User $user
     */
    public function editUser(Request $request, User $user)
    {
        $form = $this->createForm(UserType::class, $user, [
            'action' => $this->generateUrl('user_edit', ['id' => $user->getId()]),
            'method' => 'POST'
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            return new JsonResponse([
                'type' => 'success'
            ]);
        }
        return $this->render('user/form.html.twig', array(
            'user' => $user,
            'form' => $form->createView(),
        ));
    }

    /**
     * Delete a user
     *
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     *
     * @Route("/{id}/delete", name="user_delete", options={"expose" = true}, methods={"GET"})
     */
    public function deleteUser(Request $request, User $user)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($user);
        $em->flush();
        return new JsonResponse([
            'type' => 'success'
        ]);
    }
}