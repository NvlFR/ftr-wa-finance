import { useForm } from '@inertiajs/vue3';
import cloneDeep from 'lodash.clonedeep';

export function hasFiles(data) {
    return (
        data instanceof File ||
        data instanceof Blob ||
        (data instanceof FileList && data.length > 0) ||
        (data instanceof FormData && Array.from(data.values()).some((value) => hasFiles(value))) ||
        (typeof data === 'object' && data !== null && Object.values(data).some((value) => hasFiles(value)))
    );
}

export function useApiForm(rememberKeyOrData, maybeData = null) {
    const form = useForm(rememberKeyOrData, maybeData);

    let transform = (data) => data;
    let recentlySuccessfulTimeoutId = null;

    const overriders = {
        transform: (receiver) => (callback) => {
            transform = callback;
            return receiver;
        },
        submit:
            (receiver) =>
            (method, url, options = {}) => {
                // TODO: cancelToken system

                // BEFORE
                form.wasSuccessful = false;
                form.recentlySuccessful = false;
                form.clearErrors();
                clearTimeout(recentlySuccessfulTimeoutId);
                if (options.onBefore) {
                    options.onBefore();
                }

                // START
                form.processing = true;
                if (options.onStart) {
                    options.onStart();
                }

                // MAKING THE CALL
                const data = transform(form.data());
                axios[method](url, data, {
                    headers: {
                        'Content-Type': hasFiles(data) ? 'multipart/form-data' : 'application/json',
                    },
                    onUploadProgress: (event) => {
                        form.progress = event;
                        if (options.onProgress) {
                            options.onProgress(event);
                        }
                    },
                })
                    // ON SUCCESS
                    .then((response) => {
                        form.processing = false;
                        form.progress = null;
                        form.clearErrors();
                        form.wasSuccessful = true;
                        form.recentlySuccessful = true;
                        recentlySuccessfulTimeoutId = setTimeout(() => (form.recentlySuccessful = false), 2000);

                        if (options.onSuccess) {
                            options.onSuccess(response.data);
                        }

                        form.defaults(cloneDeep(form.data()));
                        form.isDirty = false;
                    })

                    // ON ERROR
                    .catch((error) => {
                        form.processing = false;
                        form.progress = null;

                        // Set validation errors
                        form.clearErrors();
                        if (error.response?.status === 422) {
                            Object.keys(error.response.data.errors).forEach((key) => {
                                form.setError(key, error.response.data.errors[key][0]);
                            });
                        }

                        if (options.onError) {
                            options.onError(error);
                        }
                    })

                    // ON FINISH
                    .finally(() => {
                        form.processing = false;
                        form.progress = null;

                        if (options.onFinish) {
                            options.onFinish();
                        }
                    });
            },
    };

    return new Proxy(form, {
        get: (target, prop, receiver) => {
            // If not overridden:
            if (Object.keys(overriders).indexOf(prop) < 0) {
                return target[prop];
            }

            return overriders[prop](receiver);
        },
    });
}
