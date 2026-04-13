<?php

/**
 * PSR-15 Middleware registration for frontend email MFA.
 *
 * This middleware runs AFTER the FrontendUserAuthenticator has authenticated
 * the user with username/password, but BEFORE the page is rendered.
 * If the user has MFA enabled and hasn't verified yet, it intercepts
 * the request and shows the code entry form.
 */
return [
    'frontend' => [
        'q23/mfa-email' => [
            'target' => \Q23\MfaEmail\Middleware\EmailMfaMiddleware::class,
            'after' => [
                'typo3/cms-frontend/authentication',
            ],
            'before' => [
                'typo3/cms-frontend/page-resolver',
            ],
        ],
    ],
];
