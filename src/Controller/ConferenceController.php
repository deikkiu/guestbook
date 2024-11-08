<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use App\Repository\ConferenceRepository;
use App\Utils\SpamChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ConferenceController extends AbstractController
{

	public function __construct(private readonly EntityManagerInterface $em)
	{
	}

	public function index(): Response
	{
		return $this->render('conference/index.html.twig');
	}

	public function show(Request $request, ConferenceRepository $conferenceRepository, CommentRepository $commentRepository, SpamChecker $checker): Response
	{
		$conferenceSlug = $request->get('slug');
		$conference = $conferenceRepository->findOneBy(['slug' => $conferenceSlug]);

		if (!$conference) {
			throw $this->createNotFoundException('Conference not found');
		}

		$comment = new Comment();

		$form = $this->createForm(CommentType::class, $comment);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$comment->setConference($conference);

			if ($photo = $form['photo']->getData()) {
				$filename = bin2hex(random_bytes(6)) . '.' . $photo->guessExtension();
				$photo->move($this->getParameter('photo_dir'), $filename);
				$comment->setPhotoFilename($filename);
			}

			$this->em->persist($comment);

			$context = [
				'user_ip' => $request->getClientIp(),
				'user_agent' => $request->headers->get('User-Agent'),
				'referer' => $request->headers->get('referer'),
				'permalink' => $request->getUri()
			];

			if ($checker->getSpamScore($comment, $context) === 2) {
				throw new \RuntimeException('Blatant spam, go away!');
			}

			$this->em->flush();

			return $this->redirectToRoute('conference', ['slug' => $conference->getSlug()]);
		}

		$offset = max(0, $request->query->getInt('offset', 0));
		$paginator = $commentRepository->getCommentPaginator($conference, $offset);

		return $this->render('conference/show.html.twig', [
			'conference' => $conference,
			'comments' => $paginator,
			'comment_form' => $form,
			'previous' => $offset - CommentRepository::COMMENTS_PER_PAGE,
			'next' => min(count($paginator), $offset + CommentRepository::COMMENTS_PER_PAGE),
		]);
	}
}
