<?php

namespace App\Controller\Admin;

use App\Entity\Comment;
use App\Entity\Conference;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends AbstractDashboardController
{
	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public function index(): Response
	{
		$adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
		return $this->redirect($adminUrlGenerator->setController(ConferenceCrudController::class)->generateUrl());

		// Option 1. You can make your dashboard redirect to some common page of your backend
		//
		// $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
		// return $this->redirect($adminUrlGenerator->setController(OneOfYourCrudController::class)->generateUrl());

		// Option 2. You can make your dashboard redirect to different pages depending on the user
		//
		// if ('jane' === $this->getUser()->getUsername()) {
		//     return $this->redirect('...');
		// }

		// Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
		// (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
		//
		// return $this->render('some/path/my-dashboard.html.twig');
	}

	public function configureDashboard(): Dashboard
	{
		return Dashboard::new()
			->setTitle('Guestbook');
	}

	public function configureMenuItems(): iterable
	{
		yield MenuItem::LinkToRoute('Back to website', 'fa fa-home', 'home');
		yield MenuItem::LinkToCrud('Conferences', 'fas fa-map-marker-alt', Conference::class);
		yield MenuItem::LinkToCrud('Comments', 'fas fa-comments', Comment::class);
	}
}
