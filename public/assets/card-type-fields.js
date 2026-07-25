(function () {
    const cardTypeSelect =
        document.getElementById('card_type_id');

    if (!cardTypeSelect) {
        return;
    }

    const scIdField =
        document.getElementById('sc-id-field');

    const philhealthField =
        document.getElementById('philhealth-field');

    const schoolField =
        document.getElementById('school-field');

    function setVisible(element, visible) {
        if (!element) {
            return;
        }

        element.hidden = !visible;
    }

    function updateCardTypeFields() {
        const selectedOption =
            cardTypeSelect.options[
                cardTypeSelect.selectedIndex
            ];

        const slug =
            selectedOption?.dataset?.slug || '';

        const isSK =
            slug === 'sangguniang-kabataan';

        setVisible(scIdField, !isSK);
        setVisible(philhealthField, !isSK);
        setVisible(schoolField, isSK);
    }

    cardTypeSelect.addEventListener(
        'change',
        updateCardTypeFields
    );

    updateCardTypeFields();
})();