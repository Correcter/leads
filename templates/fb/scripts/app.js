(function (window) {
    'use strict';
    console.log('Starting application...');
    var re = new RegExp('.+\@.+\..+');

    function createPanel( id, title, parent, status, formId) {
        return $('<div class="panel panel-default" >')
            .html(
            '<div class="panel-heading" role="tab" id="' + id + 'Heading">' +
                '<h4 class="panel-title" style="position: relative;">' +
                    '<a class="collapsed" ' +
                        'role="button" ' +
                        'data-toggle="collapse" ' +
                        'data-parent="#'+ parent +'" ' +
                        'aria-expanded="false" ' +
                        'aria-controls="' + id + '"' +
                        'href="#' + id + '">' + title + '</a>' +
                        '<span id="'+ formId +'" class="leadform '+ status.toLowerCase() + '">'+ status +'</span>' +
                '</h4>' +
            '</div>' +
            '<div id="' + id + '" ' +
                'class="panel-collapse collapse" ' +
                'role="tabpanel" ' +
                'aria-labelledby="' + id + 'Heading">' +
                '<div class="panel-body"></div>' +
            '</div>'
        );
    }
    
    function get_contact_form(pages) {

        var wrapper = $('<div class="page-list panel-group" id="page-accordion" role="tablist" aria-multiselectable="true">');

        for ( var id in pages ) {
            if ( !pages.hasOwnProperty(id) ) continue;

            var form_panel, form_panel_body,
                page = pages[id],
                forms = page.forms,
                page_panel = createPanel('pageCollapse' + id, page.name, 'page-accordion', '', 0),
                page_panel_body = $('.panel-body', page_panel),
                form_wrapper = $('<div class="form-list panel-group" id="form-accordion-'+id+'" role="tablist" aria-multiselectable="true">').appendTo(page_panel_body);

            for ( var i in forms ){
                if ( !forms.hasOwnProperty(i) ) continue;

                form_panel = createPanel('page' + id + 'Form' + i + 'Collapse', forms[i].name, 'form-accordion-'+id, forms[i].status, forms[i].id);
                form_panel_body = $('.panel-body', form_panel);

                for ( var u in forms[i].mails ) {
                    if ( !forms[i].mails.hasOwnProperty(u) ) continue;

                    form_panel_body.append(
                        $('<div class="input-group" role="group" style="margin-bottom: 10px">')
                            .html(
                                '<input class="form-control" placeholder="' + forms[i].mails[u] + '" readonly="" type="text">' +
                                '<span class="input-group-btn">' +
                                    '<button type="button" ' +
                                        'class="btn btn-danger delete-mail" ' +
                                        'data-id="'+ forms[i].id + '|' + forms[i].mails[u] +'">' +
                                        'Удалить' +
                                    '</button>' +
                                '</span>'
                            )
                    );
                }
                form_panel_body.append(
                    $('<div class="input-group add-mail-form" role="group">')
                        .html(
                            '<input class="form-control" type="text">' +
                            '<span class="input-group-btn">' +
                                '<button type="button" ' +
                                    'class="btn btn-success add-mail" ' +
                                    'data-id="'+ forms[i].id +'">' +
                                    'Добавить' +
                                '</button>' +
                            '</span>'
                        )
                );
                form_wrapper.append(form_panel);
            }
            
            wrapper.append(page_panel);
        }

        return wrapper;
    }

    window.appInit = function () {

        $('#auth_button')
            .click(
                function (e) {
                    var b, target = $(e.target).button('loading').attr('disabled', 'disabled');
                    FB.login(
                        function ( response ) {
                            if ( response.status !== 'connected' ) return;

                            // Set authorised user id
                            Social.prototype.__agency_id = response.authResponse.userID;

                            var social = new Social( FB );
                            social
                                .loadPages()
                                .then(function (pages) {
                                    social.__pages = pages;

                                    var page_button, ul = $('#list');
                                    for ( var i in social.__pages ) {
                                        if ( !social.__pages.hasOwnProperty(i) ) continue;

                                        page_button = $('<button class="list-group-item custom">')
                                            .attr('data-id', i)
                                            .text(social.__pages[i].name)
                                            .click(function ( e ) {
                                                var
                                                    t = $(e.target),
                                                    value = t.hasClass('list-group-item-success'),
                                                    id = t.attr('data-id');
                                                social
                                                    .setSubscription(social.__pages[id], !value)
                                                    .then(
                                                        function (response) {
                                                            if  ( !response || response.error ){
                                                                alert('Error:' + response.error.message);
                                                                // TODO: handle error somehow
                                                                console.log('Error while setting subscription:',  response);
                                                            } else {
                                                                if ( value ) {
                                                                    alert('Подписка успешно удалена');
                                                                    social.__pages[id].subscribed = false;
                                                                    t.removeClass('list-group-item-success');
                                                                } else {
                                                                    alert('Подписка успешно оформлена');
                                                                    social.__pages[id].subscribed = true;
                                                                    t.addClass('list-group-item-success');
                                                                }
                                                            }
                                                        }
                                                    )
                                            });

                                       // console.log(social.__pages[i]);

                                        if ( social.__pages[i].subscribed ){
                                            page_button.addClass('list-group-item-success')
                                        }
                                        ul.append(page_button);

                                        ul.append(
                                            '<span page_id="'+ social.__pages[i].id +'" title="MindBox subscribed?" class="mindbox '+
                                            (social.__pages[i].mindbox_subscribed ? "yes" : "no") +'">'+
                                                (social.__pages[i].mindbox_subscribed ? "YES" : "NO") +
                                            '</span>');
                                    }

                                    $('#collapseOne').collapse();
                                    $('#collapseTwo').collapse();
                                });

                            $('#page_submit').click(function () {

                                var target =  $(this);

                                if ( target.attr('disabled') === 'disabled' ) {
                                    return false;
                                }

                                target.button('loading').attr('disabled', 'disabled');

                                $('#contacts-form>*').remove();

                                // Set user page token
                                social.ajax(
                                    '/grab/' + social.__user_id,
                                    'get'
                                ).then(
                                    function ( response ) {

                                        social.getForms().then(
                                            function ( forms ) {
                                                get_contact_form(forms).prependTo('#contacts-form');
                                                $('a.collapse', '#contacts-form').click(
                                                    function ( e ) {
                                                        var
                                                            target  = $(e.target),
                                                            element = $('#' + target.attr('aria-controls'));
                                                        if( element.hasClass('in')  ) {
                                                            element.removeClass('in');
                                                        } else {
                                                            element.addCalss('in');
                                                        }
                                                    }
                                                );

                                                var remove_handler = function (e) {
                                                    var
                                                        target  = $(e.target),
                                                        data    = target.attr('data-id').split('|');

                                                    social.ajax(
                                                        '/user/' + social.__user_id + '/mail',
                                                        'delete',
                                                        {
                                                            data : {
                                                                form_id : data[0],
                                                                mail    : data[1]
                                                            }
                                                        }
                                                    ).then(
                                                        function ( response ) {
                                                            if ( !response || response.error ) {
                                                                console.log( 'Error while deliting mail', response );
                                                            } else {
                                                                target.closest('.input-group').remove();
                                                            }
                                                        }
                                                    )
                                                };

                                                $('.add-mail', '#contacts-form').click(
                                                    function (e) {
                                                        var
                                                            target  = $(e.target),
                                                            data    = target.attr('data-id'),
                                                            input   = $('.form-control',target.closest('.input-group')),
                                                            value   = input.val();

                                                        if ( re.test(value) ) {

                                                            var element = $('<div class="input-group" role="group" style="margin-bottom: 10px">')
                                                                .html(
                                                                    '<input class="form-control" type="text" placeholder="' + value + '" readonly disabled>' +
                                                                    '<span class="input-group-btn">' +
                                                                    '<button type="button" ' +
                                                                    'class="btn btn-danger delete-mail" ' +
                                                                    'data-id="' + data + '|' + value + '">' +
                                                                    'Удалить' +
                                                                    '</button>' +
                                                                    '</span>'
                                                                );
                                                            $('.delete-mail', element).click(remove_handler);

                                                            social.ajax(
                                                                '/user/' + social.__user_id + '/mail',
                                                                'post',
                                                                {
                                                                    data: {
                                                                        form_id: data,
                                                                        mail: value
                                                                    }
                                                                }
                                                            ).then(
                                                                function (response) {
                                                                    if (!response || response.error) {
                                                                        console.log('Error while adding mail', response);
                                                                    } else {
                                                                        console.log(element, target.parent('.panel-body'));
                                                                        target.closest('.panel-body').prepend(element);
                                                                        input.val('');
                                                                    }
                                                                }
                                                            );
                                                        }
                                                    }
                                                );

                                                $('.delete-mail', '#contacts-form').click(remove_handler);
                                                target.button('reset').attr('disabled', '');
                                                $('#collapseTwo').collapse('hide');
                                                $('#collapseThree').collapse('show');
                                            }
                                        )
                                    }
                                );
                            });
                        },{scope: 'manage_pages'}
                    )
                }
            );

        $('#back').click(function () {
            $('#collapseTwo').collapse('show');
            $('#collapseThree').collapse('hide');
        });
        
    };
})(window);