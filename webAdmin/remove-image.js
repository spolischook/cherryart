$( ".delete-image-container .delete-image-button" ).click(function() {
    var deleteLink = $(this).data('delete-link'),
        imageContainer = $(this).parent(),
        errorFlashMessage = '<div class="alert alert-danger" role="alert">' +
            '<button type="button" class="close" data-dismiss="alert" aria-label="Close">'+
            '<span aria-hidden="true">&times;</span></button>'+
            'Some error was happen. Image may not deleted</div>',
        successFlashMessage = '<div class="alert alert-success" role="alert">' +
            '<button type="button" class="close" data-dismiss="alert" aria-label="Close">'+
            '<span aria-hidden="true">&times;</span></button>Image was successfuly deleted</div>';
    $.ajax({
        url: deleteLink,
        method: 'DELETE'
    }).done(function(data, textStatus, jqXHR) {
        if (!jqXHR || !jqXHR.status || jqXHR.status != 204) {
            $('div.flash-messages').append(errorFlashMessage);
            $('html,body').animate({ scrollTop: 0 }, 'slow');
        } else {
            imageContainer.remove();
            $('div.flash-messages').append(successFlashMessage);
        }
    }).fail(function () {
        $('div.flash-messages').append(errorFlashMessage);
        $('html,body').animate({ scrollTop: 0 }, 'slow');
    });
});
