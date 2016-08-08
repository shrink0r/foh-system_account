<?php
/*
 * AUTOGENERATED CODE - DO NOT EDIT!
 *
 * This base class was generated by the Trellis library and
 * must not be modified manually. Any modifications to this
 * file will be lost upon triggering the next automatic
 * class generation.
 *
 * If you are looking for a place to alter the behaviour of
 * the 'User' type please see the skeleton
 * class 'UserType'. Modifications to the skeleton
 * file will prevail any subsequent class generation runs.
 *
 * To define new attributes or adjust existing attributes and their
 * default options modify the schema definition file of
 * the 'User' type.
 *
 * @see https://github.com/honeybee/trellis
 */

namespace Hlx\Security\User\Projection\Standard\Base;

use Trellis\Common\Options;
use Workflux\StateMachine\StateMachineInterface;
use Hlx\Security\User\Projection\Standard\Base;
use Honeybee\Projection\ProjectionType;

/**
 * Serves as the base class to the 'User' type skeleton.
 */
abstract class UserType extends ProjectionType
{
    /**
     * StateMachineInterface $workflow_state_machine
     */
    protected $workflow_state_machine;

    /**
     * Creates a new 'UserType' instance.
     *
     * @param StateMachineInterface $workflow_state_machine
     */
    public function __construct(StateMachineInterface $workflow_state_machine)
    {
        $this->workflow_state_machine = $workflow_state_machine;

        parent::__construct(
            'User',
            [
                new \Trellis\Runtime\Attribute\Text\TextAttribute(
                    'username',
                    $this,
                    []
                ),
                new \Trellis\Runtime\Attribute\Email\EmailAttribute(
                    'email',
                    $this,
                    []
                ),
                new \Trellis\Runtime\Attribute\Text\TextAttribute(
                    'role',
                    $this,
                    []
                ),
                new \Trellis\Runtime\Attribute\Text\TextAttribute(
                    'firstname',
                    $this,
                    []
                ),
                new \Trellis\Runtime\Attribute\Text\TextAttribute(
                    'lastname',
                    $this,
                    []
                ),
                new \Trellis\Runtime\Attribute\Text\TextAttribute(
                    'password_hash',
                    $this,
                    []
                ),
                new \Trellis\Runtime\Attribute\ImageList\ImageListAttribute(
                    'images',
                    $this,
                    []
                ),
                new \Trellis\Runtime\Attribute\EmbeddedEntityList\EmbeddedEntityListAttribute(
                    'tokens',
                    $this,
                    array(
                        'entity_types' => array(
                            '\\Hlx\\Security\\User\\Projection\\Standard\\Embed\\VerificationType',
                            '\\Hlx\\Security\\User\\Projection\\Standard\\Embed\\AuthenticationType',
                            '\\Hlx\\Security\\User\\Projection\\Standard\\Embed\\OauthType',
                        ),
                    )
                ),
            ],
            new Options(
                array(
                    'vendor' => 'Hlx',
                    'package' => 'Security',
                    'is_hierarchical' => false,
                )
            )
        );
    }

    /**
     * Returns an (immutable) state-machine instance responseable for controlling an entity's workflow.
     *
     * @return StateMachineInterface
     */
    public function getWorkflowStateMachine()
    {
        return $this->workflow_state_machine;
    }

    /**
     * Returns the EntityInterface implementor to use when creating new entities.
     *
     * @return string Fully qualified name of an EntityInterface implementation.
     */
    public static function getEntityImplementor()
    {
        return '\\Hlx\\Security\\User\\Projection\\Standard\\User';
    }
}
