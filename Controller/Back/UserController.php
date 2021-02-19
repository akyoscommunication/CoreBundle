<?php

namespace Akyos\CoreBundle\Controller\Back;

use Akyos\CoreBundle\Entity\User;
use Akyos\CoreBundle\Form\UserType;
use Akyos\CoreBundle\Form\UserEditType;
use Akyos\CoreBundle\Repository\UserRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/admin/user", name="user_")
 */
class UserController extends AbstractController
{
	/**
	 * @Route("/", name="index", methods={"GET"})
	 * @param UserRepository $userRepository
	 * @param PaginatorInterface $paginator
	 * @param Request $request
	 * @return Response
	 */
	public function index(UserRepository $userRepository, PaginatorInterface $paginator, Request $request): Response
	{
		$roles = $this->container->get('parameter_bag')->get('user_roles');
		$flippedRoles = array_flip($roles);

		$query = $userRepository->createQueryBuilder('a');

		$keyword = $request->query->get('search');
		if ($keyword) {
			if (array_key_exists($keyword, $roles)) {
				$keyword = $roles[$keyword];
			}
			$query
				->andWhere('a.email LIKE :keyword OR a.roles LIKE :keyword')
				->setParameter('keyword', '%' . $keyword . '%');
		}
		$els = $paginator->paginate($query->getQuery(), $request->query->getInt('page', 1), 12);

		foreach ($els as $user) {
			$newUserRoles = array_map(static function ($n) use ($flippedRoles) {
				return $flippedRoles[$n];
			}, $user->getRoles());
			$user->setRoles($newUserRoles);
		}

		return $this->render('@AkyosCore/crud/index.html.twig', [
			'els' => $els,
			'title' => 'Utilisateurs',
			'entity' => 'User',
			'route' => 'user',
			'fields' => array(
				'ID' => 'Id',
				'Email' => 'Email',
				'Rôles' => 'RolesDisplay'
			),
		]);
	}

	/**
	 * @Route("/new", name="new", methods={"GET","POST"})
	 * @param Request $request
	 * @param RoleHierarchyInterface $roleHierarchy
	 * @param UserPasswordEncoderInterface $passwordEncoder
	 * @return Response
	 */
	public function new(Request $request, RoleHierarchyInterface $roleHierarchy, UserPasswordEncoderInterface $passwordEncoder): Response
	{
		$user = new User();
		$form = $this->createForm(UserType::class, $user);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$user->setPassword(
				$passwordEncoder->encodePassword(
					$user,
					$form->get('password')->getData()
				)
			);

			$entityManager = $this->getDoctrine()->getManager();
			$entityManager->persist($user);
			$entityManager->flush();

			return $this->redirectToRoute('user_index');
		}

		return $this->render('@AkyosCore/crud/new.html.twig', [
			'el' => $user,
			'title' => 'Utilisateur',
			'entity' => 'User',
			'route' => 'user',
			'form' => $form->createView(),
		]);
	}

	/**
	 * @Route("/{id}/edit", name="edit", methods={"GET","POST"})
	 * @param Request $request
	 * @param User $user
	 * @return Response
	 */
	public function edit(Request $request,
						 User $user): Response
	{
		$form = $this->createForm(UserEditType::class, $user);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$this->getDoctrine()->getManager()->flush();

			return $this->redirectToRoute('user_index');
		}

		return $this->render('@AkyosCore/crud/edit.html.twig', [
			'el' => $user,
			'title' => 'Utilisateur',
			'entity' => 'User',
			'route' => 'user',
			'form' => $form->createView(),
		]);
	}

	/**
	 * @Route("/{id}", name="delete", methods={"DELETE"})
	 * @param Request $request
	 * @param User $user
	 * @return Response
	 */
	public function delete(Request $request, User $user): Response
	{
		if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
			$entityManager = $this->getDoctrine()->getManager();
			$entityManager->remove($user);
			$entityManager->flush();
		}

		return $this->redirectToRoute('user_index');
	}
}
