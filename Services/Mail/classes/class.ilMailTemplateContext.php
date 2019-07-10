<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

use OrgUnit\PublicApi\OrgUnitUserService;

require_once './Services/Language/classes/class.ilLanguageFactory.php';

/**
 * Class ilMailTemplateContext
 * @author  Michael Jansen <mjansen@databay.de>
 * @ingroup ServicesMail
 */
abstract class ilMailTemplateContext
{
    /** @var ilLanguage|null */
    protected $language;
    
    /** @var OrgUnitUserService */
    protected $orgUnitUserService;

    /**
     * ilMailTemplateContext constructor.
     * @param OrgUnitUserService|null $orgUnitUserService
     */
    public function __construct(
         OrgUnitUserService $orgUnitUserService = null
    ) {
        if (null === $orgUnitUserService) {
            $orgUnitUserService = new OrgUnitUserService();
        }
        $this->orgUnitUserService = $orgUnitUserService;
    }

    /**
     * @return ilLanguage|null
     */
    public function getLanguage() : ?ilLanguage
    {
        global $DIC;

        return $this->language ? $this->language : $DIC->language();
    }

    /**
     * @param ilLanguage|null $language
     */
    public function setLanguage(?ilLanguage $language) : void
    {
        $this->language = $language;
    }

    /**
     * Returns a unique (in the context of mail template contexts) id
     * @return string
     */
    abstract public function getId() : string;

    /**
     * Returns a translated title (depending on the current language) which is displayed in the user interface
     * @return string
     */
    abstract public function getTitle() : string;

    /**
     * Returns a translated description (depending on the current language) which is displayed in the user interface
     * @return string
     */
    abstract public function getDescription() : string;

    /**
     * @return array
     */
    final private function getGenericPlaceholders() : array
    {
        return [
            'mail_salutation' => [
                'placeholder' => 'MAIL_SALUTATION',
                'label' => $this->getLanguage()->txt('mail_nacc_salutation')
            ],
            'first_name' => [
                'placeholder' => 'FIRST_NAME',
                'label' => $this->getLanguage()->txt('firstname')
            ],
            'last_name' => [
                'placeholder' => 'LAST_NAME',
                'label' => $this->getLanguage()->txt('lastname')
            ],
            'login' => [
                'placeholder' => 'LOGIN',
                'label' => $this->getLanguage()->txt('mail_nacc_login')
            ],
            'title' => [
                'placeholder' => 'TITLE',
                'label' => $this->getLanguage()->txt('mail_nacc_title'),
                'supportsCondition' => true
            ],
            'firstname_last_name_superior' => [
                'placeholder' => 'FIRSTNAME_LASTNAME_SUPERIOR',
                'label' => $this->getLanguage()->txt('mail_firstname_last_name_superior')
            ],
            'ilias_url' => [
                'placeholder' => 'ILIAS_URL',
                'label' => $this->getLanguage()->txt('mail_nacc_ilias_url')
            ],
            'client_name' => [
                'placeholder' => 'CLIENT_NAME',
                'label' => $this->getLanguage()->txt('mail_nacc_client_name')
            ],
        ];
    }

    /**
     * Return an array of placeholders
     * @return array
     */
    final public function getPlaceholders() : array
    {
        $placeholders = $this->getGenericPlaceholders();
        $specific = $this->getSpecificPlaceholders();

        return $placeholders + $specific;
    }

    /**
     * Return an array of placeholders
     * @return array
     */
    abstract public function getSpecificPlaceholders() : array;

    /**
     * @param string $placeholder_id
     * @param array $context_parameters
     * @param ilObjUser|null $recipient
     * @param bool $html_markup
     * @return string
     */
    abstract public function resolveSpecificPlaceholder(
        string $placeholder_id,
        array $context_parameters,
        ilObjUser $recipient = null,
        bool $html_markup = false
    ) : string;

    /**
     * @param string $placeholder_id The unique (in the context of your class) placeholder id
     * @param array $context_parameters The context parameters given by the mail system (array of key/value pairs)
     * @param ilObjUser|null $recipient The recipient for this mail
     * @param bool $html_markup A flag whether or not the return value may contain HTML markup
     * @return string
     */
    public function resolvePlaceholder(
        string $placeholder_id,
        array $context_parameters,
        ilObjUser $recipient = null,
        bool $html_markup = false
    ) : string {
        if ($recipient !== null) {
            $this->initLanguage($recipient);
        }

        $old_lang = ilDatePresentation::getLanguage();
        ilDatePresentation::setLanguage($this->getLanguage());

        $resolved = '';

        switch (true) {
            case ('mail_salutation' == $placeholder_id && $recipient !== null):
                $resolved = $this->getLanguage()->txt('mail_salutation_n');
                switch ($recipient->getGender()) {
                    case 'f':
                        $resolved = $this->getLanguage()->txt('mail_salutation_f');
                        break;

                    case 'm':
                        $resolved = $this->getLanguage()->txt('mail_salutation_m');
                        break;

                    case 'n':
                        $resolved = $this->getLanguage()->txt('mail_salutation_n');
                        break;
                }
                break;

            case ('first_name' == $placeholder_id && $recipient !== null):
                $resolved = $recipient->getFirstname();
                break;

            case ('last_name' == $placeholder_id && $recipient !== null):
                $resolved = $recipient->getLastname();
                break;

            case ('login' == $placeholder_id && $recipient !== null):
                $resolved = $recipient->getLogin();
                break;

            case ('title' == $placeholder_id && $recipient !== null):
                $resolved = $recipient->getUTitle();
                break;

            case 'ilias_url' == $placeholder_id:
                $resolved = ILIAS_HTTP_PATH . '/login.php?client_id=' . CLIENT_ID;
                break;

            case 'client_name' == $placeholder_id:
                $resolved = CLIENT_NAME;
                break;

            case 'firstname_last_name_superior' == $placeholder_id && $recipient !== null:
                $ouUsers = $this->orgUnitUserService->getUsers([$recipient->getId()], true);
                foreach ($ouUsers as $ouUser) {
                    $superiors = $ouUser->getSuperiors();

                    $firstAndLastNames = \ilUserUtil::getNamePresentation(
                      array_map(function(OrgUnit\User\ilOrgUnitUser $ouUser) {
                          return $ouUser->getUserId();
                      }, $superiors),
                      false, false, '', false, true, false
                    );

                    $resolved = implode(', ', $firstAndLastNames);
                    break;
                }
                break;

            case !in_array($placeholder_id, array_keys($this->getGenericPlaceholders())):
                $resolved = $this->resolveSpecificPlaceholder(
                    $placeholder_id,
                    $context_parameters,
                    $recipient,
                    $html_markup
                );
                break;
        }

        ilDatePresentation::setLanguage($old_lang);

        return $resolved;
    }

    /**
     * @param ilObjUser $user
     */
    protected function initLanguage(ilObjUser $user) : void
    {
        $this->initLanguageByIso2Code($user->getLanguage());
    }

    /**
     * @param string $languageCode
     */
    protected function initLanguageByIso2Code(string $languageCode) : void
    {
        $this->language = ilLanguageFactory::_getLanguage($languageCode);
        $this->language->loadLanguageModule('mail');
    }
}