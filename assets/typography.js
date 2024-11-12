setTimeout(function() {
    if ( document.querySelectorAll(".aegis-section") ) {
        const blocks = document.querySelectorAll(".aegis-section");
        blocks.forEach(block => {
            const attributes = block.getAttribute('data-attributes');
            var attributesObj = JSON.parse(attributes);
            if ( attributesObj.tabletFontSize ) {
                block.style.setProperty('--font-size-tablet', attributesObj.tabletFontSize);
            }
            if ( attributesObj.mobileFontSize ) {
                block.style.setProperty('--font-size-mobile', attributesObj.mobileFontSize);
            }
            if ( attributesObj.tabletFontColor ) {
                console.log(attributesObj.tabletFontColor);
                block.style.setProperty('--font-color-tablet', attributesObj.tabletFontColor);
            }
            if ( attributesObj.mobileFontColor ) {
                block.style.setProperty('--font-color-mobile', attributesObj.mobileFontColor);
            }
            if ( attributesObj.tabletBGColor ) {
                block.style.setProperty('--bgcolor-tablet', attributesObj.tabletBGColor);
            }
            if ( attributesObj.mobileBGColor ) {
                block.style.setProperty('--bgcolor-mobile', attributesObj.mobileBGColor);
            }
        });
    }
}, 1000);