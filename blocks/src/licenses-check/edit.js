/**
 * Copyright (C) 2020-2023 Darko Gjorgjijoski <https://darkog.com>
 * All Rights Reserved.
 * Licensed under GPLv3.
 */

import "./style.scss"
import {InspectorControls, useBlockProps} from '@wordpress/block-editor';

import {CheckboxControl, PanelBody, PanelRow} from '@wordpress/components';
import {useEffect, useState} from "@wordpress/element";

import { __ } from '@wordpress/i18n';


const Edit = ({attributes, setAttributes}) => {

    const blockProps = useBlockProps();

    const [isEmailRequired, setEmailRequired] = useState(attributes?.emailRequired);

    useEffect(() => {
        setAttributes({emailRequired: isEmailRequired});
    }, [isEmailRequired])

    return (

        <>

            <InspectorControls>
                <PanelBody title={__('Form Settings', 'digital-license-manager')} initialOpen={false}>
                    <PanelRow>
                        <CheckboxControl
                            label={__('Require email match for guests', 'digital-license-manager')}
                            help={__('If checked, a field for email will be displayed in the form and verified if it owns the license', 'digital-license-manager')}
                            checked={isEmailRequired}
                            onChange={setEmailRequired}
                        />
                    </PanelRow>
                </PanelBody>
            </InspectorControls>


            <div {...blockProps}>
                <div className="dlm-block-licenses-check">
                    <form>
                        {isEmailRequired && <div className="dlm-form-row">
                            <label htmlFor="email">{__('Email', 'digital-license-manager')}</label>
                            <input readOnly={true} type="text" id="email" name="email" className="dlm-form-control"/>
                        </div>}
                        <div className="dlm-form-row">
                            <label htmlFor="licenseKey">{__('License Key', 'digital-license-manager')}</label>
                            <input readOnly={true} type="text" id="licenseKey" name="licenseKey" className="dlm-form-control"/>
                        </div>
                        <button disabled={true} type="submit">{__('Submit', 'digital-license-manager')}</button>
                    </form>
                </div>
            </div>
        </>
    );
};
export default Edit;
