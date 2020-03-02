$(document).ready(function(){

    $('.quantity-right-plus').click(function(e){

        var el = $(this);

        changeInputValue(el, 'plus');
    });

    $('.quantity-left-minus').click(function(e){

        var el = $(this);
        changeInputValue(el, 'minus');
    });

});

function changeInputValue(el, type) {

    var inputName = el.data('field'),
        input = null;

    if (!inputName) {
        input = el.parent().parent('.input-group').children('input');
    }
    else {
        input = el.parent().parent('.input-group').children('#'+inputName);
    }

    if (input) {
        var maxVal = null,
            newQuantity = null;
        if (type == 'plus') {
            newQuantity = parseFloat(input.val()) + 1;
            maxVal = input.attr('max')
        }
        else {
            newQuantity = parseFloat(input.val()) - 1;
        }

        if (
            (!maxVal || newQuantity <= maxVal) &&
            newQuantity > 0
        ) {
            input.val(newQuantity);
            input.change();
        }

    }
    else {
        console.log('not found input element!')
    }
}
