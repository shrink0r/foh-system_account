<?php

namespace Hlx\Security\User\Controller;

use Carbon\Carbon;
use Hlx\Security\User\Model\Aggregate\UserType;
use Hlx\Security\User\Model\Task\LogoutUser\UserLoggedOutEvent;
use Hlx\Security\User\Model\Task\ModifyUser\UserModifiedEvent;
use Hlx\Security\User\Model\Task\ProceedUserWorkflow\UserWorkflowProceededEvent;
use Hlx\Security\User\Model\Task\RegisterOauthUser\OauthUserRegisteredEvent;
use Hlx\Security\User\Model\Task\RegisterUser\UserRegisteredEvent;
use Hlx\Security\User\Model\Task\SetUserPassword\UserPasswordSetEvent;
use Hlx\Security\User\Model\Task\SetUserPassword\UserPasswordSetStartedEvent;
use Hlx\Security\User\Model\Task\UpdateOauthUser\OauthUserUpdatedEvent;
use Honeybee\Infrastructure\DataAccess\Storage\StorageReaderMap;
use Honeybee\Infrastructure\Template\TemplateRendererInterface;
use Symfony\Component\HttpFoundation\Request;

class HistoryController
{
    protected $userType;

    protected $templateRenderer;

    public function __construct(
        UserType $userType,
        TemplateRendererInterface $templateRenderer,
        StorageReaderMap $storageReaderMap
    ) {
        $this->userType = $userType;
        $this->templateRenderer = $templateRenderer;
        $this->storageReaderMap = $storageReaderMap;
    }

    public function read(Request $request)
    {
        $eventStream = $this->storageReaderMap
            ->getItem($this->userType->getPrefix().'::event_stream::event_source::reader')
            ->read($request->get('identifier'));

        $historyData = [];
        foreach ($eventStream->getEvents()->reverse() as $event) {
            $sentiment = '';
            if ($event instanceof UserRegisteredEvent) {
                $type = 'create';
                $sentiment = 'success';
                $title = 'User registered';
                $icon = 'glyphicon-plus';
            } elseif ($event instanceof OauthUserRegisteredEvent) {
                $type = 'create';
                $sentiment = 'success';
                $title = 'Oauth user registered';
                $icon = 'glyphicon-plus';
            } elseif ($event instanceof UserModifiedEvent) {
                $type = 'modify';
                $sentiment = 'success';
                $title = 'User data was modified';
                $icon = 'glyphicon-pencil';
            } elseif ($event instanceof OauthUserUpdatedEvent) {
                $type = 'modify';
                $sentiment = 'success';
                $title = 'User was modified via Oauth';
                $icon = 'glyphicon-pencil';
            } elseif ($event instanceof UserWorkflowProceededEvent) {
                $title = 'User was ';
                if ($event->getWorkflowState() === 'verified') {
                    $sentiment = 'success';
                    $type = 'promote';
                    $title .= 'verified';
                    $icon = 'glyphicon-ok';
                } elseif ($event->getWorkflowState() === 'deactivated') {
                    $sentiment = 'warning';
                    $type = 'demote';
                    $title .= 'deactivated';
                    $icon = 'glyphicon-lock';
                } else {
                    $sentiment = 'danger';
                    $title .= 'deleted';
                    $type = 'delete';
                    $icon = 'glyphicon-trash';
                }
            } elseif ($event instanceof UserPasswordSetStartedEvent) {
                $type = 'modify';
                $sentiment = 'success';
                $title = 'User password set started';
                $icon = 'glyphicon-lock';
            } elseif ($event instanceof UserPasswordSetEvent) {
                $type = 'modify';
                $sentiment = 'success';
                $title = 'User password set';
                $icon = 'glyphicon-lock';
            } elseif ($event instanceof UserLoggedOutEvent) {
                $type = 'modify';
                $sentiment = 'success';
                $title = 'User logged out';
                $icon = 'glyphicon-lock';
            }
            $historyData[] = [
                'type' => $type,
                'badge' => [ 'icon' => $icon, 'sentiment' => $sentiment ],
                'title' => $title,
                'when' => (new Carbon($event->getIsoDate()))->diffForHumans(),
                'changes' => json_encode($event->getData(), JSON_PRETTY_PRINT),
                'revision' => $event->getSeqNumber(),
                'date' => $event->getDateTime()->format('Y-m-d H:i:s')
            ];
        }

        return $this->templateRenderer->render(
            '@hlx-security/user/history.html.twig',
            [ 'history' => $historyData ]
        );
    }
}
