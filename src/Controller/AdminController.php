<?php
namespace App\Controller;

use App\Entity\User;
use App\Entity\Game;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
// Dashboard
#[Route('/', name: 'admin_dashboard')]
public function dashboard(ManagerRegistry $doctrine): Response
{
    $em = $doctrine->getManager();

    $userRepo = $em->getRepository(User::class);
    $gameRepo = $em->getRepository(Game::class);

    $totalUsers = count($userRepo->findAll());
    $totalGames = count($gameRepo->findAll());
    $totalWins = count($gameRepo->findBy(['winOrLose' => 'win']));
    $totalLosses = count($gameRepo->findBy(['winOrLose' => 'lose']));

    return $this->render('admin/dashboard.html.twig', [
        'totalUsers' => $totalUsers,
        'totalGames' => $totalGames,
        'totalWins' => $totalWins,
        'totalLosses' => $totalLosses,
    ]);
}
    // Liste des utilisateurs
    #[Route('/utilisateurs', name: 'admin_users')]
    public function users(ManagerRegistry $doctrine): Response
    {
        $users = $doctrine->getRepository(User::class)->findAll();

        return $this->render('admin/users.html.twig', [
            'users' => $users,
        ]);
    }

    // Voir un utilisateur
    #[Route('/utilisateur/{id}', name: 'admin_user_show')]
    public function showUser(User $user): Response
    {
        return $this->render('admin/user.html.twig', [
            'user' => $user,
            'games' => $user->getGames(),
        ]);
    }

    // Supprimer un utilisateur
    #[Route('/utilisateur/{id}/supprimer', name: 'admin_user_delete', methods: ['POST'])]
    public function deleteUser(User $user, ManagerRegistry $doctrine, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete-' . $user->getId(), $request->request->get('_token'))) {

            if ($user === $this->getUser()) {
                $this->addFlash('danger', ' Vous ne pouvez pas vous supprimer vous-même !');
                return $this->redirectToRoute('admin_users');
            }

            $em = $doctrine->getManager();
            foreach ($user->getGames() as $game) {
                $em->remove($game);
            }
            $em->remove($user);
            $em->flush();

            $this->addFlash('success', ' Utilisateur "' . $user->getUsername() . '" supprimé.');
        }

        return $this->redirectToRoute('admin_users');
    }

    #[Route('/utilisateur/{id}/modifier', name: 'admin_user_edit')]
    public function editUser(User $user, Request $request, ManagerRegistry $doctrine): Response
    {
        $form = $this->createFormBuilder($user)
            ->add('username', TextType::class, [
                'label' => 'Nom d\'utilisateur'
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Rôle',
                'choices' => [
                    'Utilisateur' => 'ROLE_USER',
                    'Administrateur' => 'ROLE_ADMIN',
                ],
                'multiple' => true,
                'expanded' => true,
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $doctrine->getManager()->flush();
            $this->addFlash('success', 'Utilisateur modifié.');
            return $this->redirectToRoute('admin_user_show', [
                'id' => $user->getId()
            ]);
        }

        return $this->render('admin/user_edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route('/partie/{id}/modifier', name: 'admin_game_edit')]
    public function editGame(Game $game, Request $request, ManagerRegistry $doctrine): Response
    {
        $form = $this->createFormBuilder($game)
            ->add('difficulty', TextType::class)
            ->add('guessNumber', IntegerType::class)
            ->add('winOrLose', ChoiceType::class, [
                'choices' => [
                    'Victoire' => 'win',
                    'Défaite' => 'lose'
                ]
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $doctrine->getManager()->flush();
            $this->addFlash('success', 'Partie modifiée.');
            return $this->redirectToRoute('admin_user_show', [
                'id' => $game->getPlayer()->getId() 
            ]);
        }

        return $this->render('admin/game_edit.html.twig', [
            'form' => $form->createView(),
            'game' => $game
        ]);
    }
    #[Route('/partie/{id}/supprimer', name: 'admin_game_delete', methods: ['POST'])]
    public function deleteGame(Game $game, ManagerRegistry $doctrine, Request $request): Response
    {
        $playerId = $game->getPlayer()->getId(); 

        if ($this->isCsrfTokenValid('delete-game-' . $game->getId(), $request->request->get('_token'))) {
            $em = $doctrine->getManager();
            $em->remove($game);
            $em->flush();
            $this->addFlash('success', 'Partie supprimée.');
        }

        return $this->redirectToRoute('admin_user_show', ['id' => $playerId]); 
    }
}