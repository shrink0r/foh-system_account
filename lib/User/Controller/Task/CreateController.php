<?php

namespace Hlx\Security\User\Controller\Task;

use Hlx\Security\Service\AccountService;
use Honeybee\Infrastructure\Template\TemplateRendererInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class CreateController
{
    protected $formFactory;

    protected $templateRenderer;

    protected $urlGenerator;

    protected $translator;

    protected $userService;

    protected $accountService;

    public function __construct(
        FormFactoryInterface $formFactory,
        TemplateRendererInterface $templateRenderer,
        UrlGeneratorInterface $urlGenerator,
        TranslatorInterface $translator,
        UserProviderInterface $userService,
        AccountService $accountService
    ) {
        $this->formFactory = $formFactory;
        $this->templateRenderer = $templateRenderer;
        $this->urlGenerator = $urlGenerator;
        $this->translator = $translator;
        $this->userService = $userService;
        $this->accountService = $accountService;
    }

    public function read(Request $request)
    {
        $form = $this->buildForm();

        return $this->templateRenderer->render(
            '@hlx-security/user/task/create.html.twig',
            [ 'form' => $form->createView() ]
        );
    }

    public function write(Request $request, Application $app)
    {
        $form = $this->buildForm();
        $form->handleRequest($request);

        if (!$form->isValid()) {
            return $this->templateRenderer->render(
                '@hlx-security/user/task/create.html.twig',
                [ 'form' => $form->createView() ]
            );
        }

        $formData = $form->getData();
        $username = $formData['username'];
        $email = $formData['email'];

        try {
            if (!$this->userService->userExists($username, $email)) {
                $this->accountService->registerUser($formData);
                return $app->redirect($this->urlGenerator->generate('hlx.security.user.list'));
            }
        } catch (AuthenticationException $error) {
            $errors = (array) $error->getMessageKey();
        }

        return $this->templateRenderer->render(
            '@hlx-security/user/task/create.html.twig',
            [
                'form' => $form->createView(),
                'errors' => isset($errors) ? $errors : [ 'This user is already registered.' ]
            ]
        );
    }

    protected function buildForm()
    {
        $availableRoles = $this->accountService->getAvailableRoles();
        $availableLocales = $this->translator->getFallbackLocales();

        return $this->formFactory->createBuilder(FormType::CLASS, [], [ 'translation_domain' => 'form' ])
            ->add('username', TextType::CLASS, [ 'constraints' => [ new NotBlank, new Length([ 'min' => 4 ]) ] ])
            ->add('email', EmailType::CLASS, [ 'constraints' => new NotBlank ])
            ->add('locale', ChoiceType::CLASS, [
                'choices' => array_combine($availableLocales, $availableLocales),
                'constraints' => new Choice($availableLocales),
                'translation_domain' => 'locale'
            ])
            ->add('firstname', TextType::CLASS, [ 'required' => false ])
            ->add('lastname', TextType::CLASS, [ 'required' => false ])
            ->add('role', ChoiceType::CLASS, [
                'choices' => array_combine($availableRoles, $availableRoles),
                'constraints' => new Choice($availableRoles),
                'translation_domain' => 'role'
            ])
            ->getForm();
    }
}
