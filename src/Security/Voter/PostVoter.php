<?php

namespace App\Security\Voter;

use App\Entity\Post;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class PostVoter extends Voter
{
    protected function supports(string $attribute, mixed $subject): bool
    {

        return \in_array($attribute, [VoterAction::EDIT, VoterAction::VIEW, VoterAction::DELETE])
            && $subject instanceof Post;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case VoterAction::EDIT:
                return $this->canEdit($subject, $user);

            case VoterAction::VIEW:
                return $this->canView($subject, $user);

            case  VoterAction::DELETE:
                return $this->canDelete($subject, $user);
        }

        return false;
    }

    private function canEdit(Post $post, UserInterface $user): bool
    {
        if ($post->getCreatedBy() === $user) {
            return true;
        }

        return false;
    }

    private function canView(Post $post, UserInterface $user): bool
    {
        return true;
    }

    private function canDelete(Post $post, UserInterface $user): bool
    {
        if ($post->getCreatedBy() === $user) {
            return true;
        }

        return false;
    }
}
