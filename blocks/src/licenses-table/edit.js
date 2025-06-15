/**
 * Copyright (C) 2020-present Darko Gjorgjijoski <https://darkog.com>
 * All Rights Reserved.
 * Licensed under GPLv3.
 */

import "./editor.scss"

import {
    useBlockProps,
    InspectorControls
} from '@wordpress/block-editor';


import {
    PanelBody,
    PanelRow,
    SelectControl
} from '@wordpress/components';

import {useEffect, useState} from "@wordpress/element";

import { __ } from '@wordpress/i18n';


const Edit = ({attributes, setAttributes}) => {
    
    const statuses = [
        { 'label': __( 'All', 'digital-license-manager' ), 'value' : 'all' },
        { 'label' : __( 'Valid', 'digital-license-manager' ), 'value' : 'valid' },
        { 'label' : __( 'Invalid', 'digital-license-manager' ), 'value' : 'invalid' },
        { 'label' : __( 'Expired', 'digital-license-manager' ), 'value' : 'expired' }
    ];

    const blockProps = useBlockProps();

    const [statusFilter, setStatusFilter] = useState(attributes?.statusFilter);

    useEffect(() => {
        setAttributes({'statusFilter': statusFilter});
    }, [statusFilter])

    return (

        <>
            <InspectorControls>
                <PanelBody title={__('Data Query', 'digital-license-manager')} initialOpen={false}>
                    <PanelRow>
                        <SelectControl
                            label={__('Status', 'digital-license-manager')}
                            value={statusFilter}
                            options={statuses}
                            onChange={setStatusFilter}
                            __nextHasNoMarginBottom
                        />
                    </PanelRow>
                </PanelBody>
            </InspectorControls>

            <div {...blockProps}>
                <div className="dlm-block-licenses-table">
                    <table className="dlm-block-licenses-table--preview">
                        <thead>
                        <tr>
                            <th>License</th>
                            <th>Status</th>
                            <th>Expires At</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>AAAA-DEMO-0001</td>
                            <td>Active</td>
                            <td>04 Jan 2025</td>
                        </tr>
                        <tr>
                            <td>AAAA-DEMO-0002</td>
                            <td>Expired</td>
                            <td>10 Jun 2025</td>
                        </tr>
                        <tr>
                            <td>AAAA-DEMO-003</td>
                            <td>Active</td>
                            <td>15 Jul 2025</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </>
    );
};
export default Edit;
