// global-setup.ts
import { request, type FullConfig } from '@playwright/test';
import { RequestUtils } from '@wordpress/e2e-test-utils-playwright';

async function globalSetup(config: FullConfig) {
    const { baseURL, storageState } = config.projects[0].use;

    // 1. Create a request context
    const requestContext = await request.newContext({
        baseURL,
    });

    // 2. Instantiate RequestUtils
    // We cast to 'any' for the options or pass an empty object 
    // because login() will handle the cookie creation.
    const requestUtils = new RequestUtils(requestContext, {});

    // 3. Perform login
    await requestUtils.login();

    // 4. Save the storage state to the path defined in your config
    if (typeof storageState === 'string') {
        await requestContext.storageState({ path: storageState });
    }

    await requestContext.dispose();
}

export default globalSetup;