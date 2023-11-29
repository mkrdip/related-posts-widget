/**
 * External dependencies
 */
 import { includes } from 'lodash';
 import classnames from 'classnames';
 
 /**
  * WordPress dependencies
  */
 import { useState, useEffect, useRef } from '@wordpress/element';
 import apiFetch from '@wordpress/api-fetch';
 import { addQueryArgs } from '@wordpress/url';
 import { __ } from '@wordpress/i18n';
 import { TextControl, ToggleControl, PanelBody, RadioControl, QueryControls, Disabled } from '@wordpress/components';
 import { InspectorControls, RichText, useBlockProps } from '@wordpress/block-editor';
 import ServerSideRender from '@wordpress/server-side-render';
 
 /**
  * Module Constants
  */
 const CATEGORIES_LIST_QUERY = {
     per_page: -1,
 };
 
 export default function Edit( { attributes, setAttributes } ) {
     const {
         hideTitle, title, titleLink, titleLevel,
         disableThemeStyles,
         showPostCounts,
         displayAsDropdown,
         groupBy,
         order, orderBy,
         categories 
     } = attributes;
     const titleTagName = 'h' + titleLevel;
     const blockProps = useBlockProps( {
         className:  disableThemeStyles ? 'widget-title' : '',
     } );
     const [ categoriesList, setCategoriesList ] = useState( [] );
     const categorySuggestions = categoriesList.reduce(
         ( accumulator, category ) => ( {
             ...accumulator,
             [ category.name ]: category,
         } ),
         {}
     );
     const selectCategories = ( tokens ) => {
         const hasNoSuggestion = tokens.some(
             ( token ) =>
                 typeof token === 'string' && ! categorySuggestions[ token ]
         );
         if ( hasNoSuggestion ) {
             return;
         }
         // Categories that are already will be objects, while new additions will be strings (the name).
         // allCategories nomalizes the array so that they are all objects.
         const allCategories = tokens.map( ( token ) => {
             return typeof token === 'string'
                 ? categorySuggestions[ token ]
                 : token;
         } );
         // We do nothing if the category is not selected
         // from suggestions.
         if ( includes( allCategories, null ) ) {
             return false;
         }
         setAttributes( { categories: allCategories } );
     };
 
     // Suggestion list
     const isStillMounted = useRef();
 
     useEffect( () => {
         isStillMounted.current = true;
 
         apiFetch( {
             path: addQueryArgs( `/wp/v2/categories`, CATEGORIES_LIST_QUERY ),
         } )
             .then( ( data ) => {
                 if ( isStillMounted.current ) {
                     setCategoriesList( data );
                 }
             } )
             .catch( () => {
                 if ( isStillMounted.current ) {
                     setCategoriesList( [] );
                 }
             } );
 
         return () => {
             isStillMounted.current = false;
         };
     }, [] );
 
     const coreBlocks = wp.blocks.getBlockTypes().filter( ( block ) => {
         return (
             block.name === 'core/query'
         );
     } )
     //console.log(coreBlocks[0].settings.edit);
     //return coreBlocks[0].edit;
 
     return (
         <>
             <InspectorControls>
                 <PanelBody title={ __( 'Title' ) }>
                     <ToggleControl
                         label={ __( 'Hide title' ) }
                         checked={ hideTitle }
                         onChange={ () =>
                             setAttributes( {
                                 hideTitle: ! hideTitle,
                             } )
                         }
                     />
                     <TextControl
                         label={ __( 'Title' ) }
                         value={ title }
                         onChange={ ( title ) =>
                             setAttributes( {
                                 title,
                             } ) }
                     />
                     <ToggleControl
                         label={ __( 'Make widget title link' ) }
                         checked={ titleLink }
                         onChange={ () =>
                             setAttributes( {
                                 titleLink: ! titleLink,
                             } )
                         }
                     />
                 </PanelBody>
                 <PanelBody title={ __( 'Filter' ) }>
                     <RadioControl
                         label={ __( 'Group by' ) }
                         selected={ groupBy }
                         options={ [
                             { label: 'Month', value: 'monthly' },
                             { label: 'Year', value: 'yearly' },
                         ] }
                         onChange={ ( groupBy ) =>
                             setAttributes( {
                                 groupBy,
                             } )
                         }
                     />
                     <ToggleControl
                         label={ __( 'Display as dropdown' ) }
                         checked={ displayAsDropdown }
                         onChange={ () =>
                             setAttributes( {
                                 displayAsDropdown: ! displayAsDropdown,
                             } )
                         }
                     />
                     <ToggleControl
                         label={ __( 'Show post counts' ) }
                         checked={ showPostCounts }
                         onChange={ () =>
                             setAttributes( {
                                 showPostCounts: ! showPostCounts,
                             } )
                         }
                     />
                     <QueryControls
                         { ...{ order, orderBy } }
                         onOrderChange={ ( value ) =>
                             setAttributes( { order: value } )
                         }
                         onOrderByChange={ ( value ) =>
                             setAttributes( { orderBy: value } )
                         }
                         categorySuggestions={ categorySuggestions }
                         onCategoryChange={ selectCategories }
                         selectedCategories={ categories }
                     />
                 </PanelBody>
                 <PanelBody title={ __( 'Post details' ) }>
                 </PanelBody>
                 <PanelBody title={ __( 'General' ) }>
                     <ToggleControl
                         label={ __( 'Disable the built-in CSS' ) }
                         checked={ disableThemeStyles }
                         onChange={ () =>
                             setAttributes( {
                                 disableThemeStyles: ! disableThemeStyles,
                             } )
                         }
                     />
                     <ToggleControl
                         label={ __( 'Disable only font styles' ) }
                         checked={ disableThemeStyles }
                         onChange={ () =>
                             setAttributes( {
                                 disableThemeStyles: ! disableThemeStyles,
                             } )
                         }
                     />
                     <ToggleControl
                         label={ __( 'Disable Theme\'s styles' ) }
                         checked={ disableThemeStyles }
                         onChange={ () =>
                             setAttributes( {
                                 disableThemeStyles: ! disableThemeStyles,
                             } )
                         }
                     />
                 </PanelBody>
                 <PanelBody title={ __( 'Footer' ) }>
                 </PanelBody>
             </InspectorControls>
             <div 
                 { ...useBlockProps() } 
                 className={ blockProps.className }
             >
                 <Disabled>
                     <ServerSideRender
                         block="tiptip/same-posts-block"
                         attributes={ attributes }
                     />
                 </Disabled>
             </div>
         </>
     );
 }
 