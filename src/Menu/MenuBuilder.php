<?php

namespace App\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

class MenuBuilder
{
    public function __construct(
        private FactoryInterface $factory,
        private Security $security,
        private RequestStack $requestStack,
        private TranslatorInterface $translator,
        private array $enabledLocales
    ) {
    }

    public function createMainMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttribute('class', 'navbar-nav');

        $menu->addChild('Begin', ['route' => 'app_index'])
            ->setAttribute('class', 'nav-item')
            ->setLinkAttribute('class', 'nav-link subnav-link')
            ->setLabel($this->translator->trans('Begin'));

        $menu->addChild('Search', ['route' => 'app_search_index'])
            ->setAttribute('class', 'nav-item')
            ->setLinkAttribute('class', 'nav-link subnav-link')
            ->setLabel($this->translator->trans('Search'));

        if ($this->security->isGranted('ROLE_ADMIN')) {
            $menu->addChild('Patients', ['route' => 'app_patient_index'])
                ->setAttribute('class', 'nav-item')
                ->setLinkAttribute('class', 'nav-link subnav-link')
                ->setLabel($this->translator->trans('Patients'));

            $menu->addChild('Hospitalized', ['route' => 'app_hospitalized_index'])
                ->setAttribute('class', 'nav-item')
                ->setLinkAttribute('class', 'nav-link subnav-link')
                ->setLabel($this->translator->trans('Hospitalized'));

            $menu->addChild('Appointments', ['route' => 'app_appointment_index'])
                ->setAttribute('class', 'nav-item')
                ->setLinkAttribute('class', 'nav-link subnav-link')
                ->setLabel($this->translator->trans('Appointments'));

            $menu->addChild('Attendances', ['route' => 'app_attendance_index'])
                ->setAttribute('class', 'nav-item')
                ->setLinkAttribute('class', 'nav-link subnav-link')
                ->setLabel($this->translator->trans('Attendances'));
        }

        $menu->addChild('Visitors', ['route' => 'app_visitor_index'])
            ->setAttribute('class', 'nav-item')
            ->setLinkAttribute('class', 'nav-link subnav-link')
            ->setLabel($this->translator->trans('Visitors'));

        $menu->addChild('Stakeholders', ['route' => 'app_stakeholder_index'])
            ->setAttribute('class', 'nav-item')
            ->setLinkAttribute('class', 'nav-link subnav-link')
            ->setLabel($this->translator->trans('Stakeholders'));

        if ($this->security->isGranted('ROLE_ADMIN')) {
            $menu->addChild('Users', ['route' => 'app_user_index'])
                ->setAttribute('class', 'nav-item')
                ->setLinkAttribute('class', 'nav-link subnav-link')
                ->setLabel($this->translator->trans('Users'));
        }

        // Language Switcher
        $request = $this->requestStack->getCurrentRequest();
        $currentLocale = $request ? $request->getLocale() : 'en';
        $currentRoute = $request ? $request->attributes->get('_route') : 'app_index';
        $currentRouteParams = $request ? $request->attributes->get('_route_params', []) : [];

        foreach ($this->enabledLocales as $locale) {
            if ($locale !== $currentLocale) {
                $params = array_merge($currentRouteParams, ['_locale' => $locale]);
                $menu->addChild('lang_' . $locale, [
                    'route' => $currentRoute,
                    'routeParameters' => $params
                ])
                ->setAttribute('class', 'nav-item')
                ->setLinkAttribute('class', 'nav-link subnav-link')
                ->setLabel('<i class="bi bi-translate"></i><b class="text-uppercase">' . $locale . '</b>')
                ->setExtra('safe_label', true);
            }
        }

        // User Submenu
        $user = $this->security->getUser();
        if ($user) {
            $menu->addChild('User', [
                'route' => 'app_user_show',
                'routeParameters' => ['id' => $user->getId()]
            ])
            ->setAttribute('class', 'nav-item')
            ->setLinkAttribute('class', 'nav-link subnav-link')
            ->setLabel('<i class="icon-user"></i> (' . $user->getUserIdentifier() . ')')
            ->setExtra('safe_label', true);

            $menu->addChild('Logout', ['route' => 'app_logout'])
                ->setAttribute('class', 'nav-item')
                ->setLinkAttribute('class', 'nav-link subnav-link')
                ->setLabel('<i class="bi bi-box-arrow-left"></i>')
                ->setExtra('safe_label', true);
        } else {
            $menu->addChild('Login', ['route' => 'app_login'])
                ->setAttribute('class', 'nav-item')
                ->setLinkAttribute('class', 'nav-link subnav-link')
                ->setLabel('<i class="bi bi-box-arrow-in-right"></i>')
                ->setExtra('safe_label', true);
        }

        return $menu;
    }
}
