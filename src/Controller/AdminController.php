<?php
namespace App\Controller;

use App\Entity\User;
use App\Entity\Game;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
}