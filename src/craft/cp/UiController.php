<?php

declare(strict_types=1);

namespace parallelogram\craftkit\craft\cp;

use craft\web\Controller;
use Throwable;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

/**
 * The CP UI kit's one action, added to each plugin that calls {@see CpUi::register()}: saves the
 * Simple / Advanced switch to the signed-in admin's preferences, at `{plugin}/pk-ui/advanced`.
 *
 * Yii checks the CSRF token and Craft requires a signed-in user before the action runs.
 *
 * @internal
 */
final class UiController extends Controller
{
    /**
     * Where preferences are read and saved; the signed-in user's when not set.
     */
    public ?PreferenceStoreInterface $store = null;

    /**
     * @throws BadRequestHttpException When the request isn't a POST.
     * @throws ForbiddenHttpException When the user isn't an admin.
     * @throws Throwable When Craft can't read or save the user's preferences.
     */
    public function actionAdvanced(): Response
    {
        $request = $this->request;

        if (! $request->getIsPost()) {
            throw new BadRequestHttpException('Switching Advanced mode needs a POST request.');
        }

        $store = $this->store ?? CraftPreferenceStore::current();

        if (! $store->isAdmin()) {
            throw new ForbiddenHttpException('Only admins can switch Advanced mode.');
        }

        $on = AdvancedMode::isOn($request->getBodyParam('advanced'));
        $store->save(AdvancedMode::PREFERENCE, $on);

        return $this->asJson(['advanced' => $on]);
    }
}
