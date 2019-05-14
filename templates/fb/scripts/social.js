(function (window) {
    'use strict';
    
    var Social = function ( fb ) {

        console.log('Init social... getting user data');

        this.__fb       = fb;
        this.__base_url = window.location.protocol + "//" + window.location.host;

    };
    
    Social.prototype = {
        __agency_id : 0,
        __fb        : null,
        __user_id   : null,
        __user_name : null,
        __pages     : null,
        __base_url  : null,

        status      : 0,

        loadPages : function () {
            var __self = this;

            return this.FBcall('/me?fields=id,name')
                .then(
                    function ( response ) {

                        __self.__user_id    = response.id;
                        __self.__user_name  = response.name;

                        __self.ajax(
                            '/user/' + __self.__user_id,
                            'put',
                            { name: __self.__user_name}
                        );
                        // if( __self.__agency_id != __self.__user_id ) {
                            return __self.FBcall('/me/accounts?fields=id,name,access_token&limit=1000');
                        // }
                    }
                ).then(
                    function ( response ) {
                         // if( __self.__agency_id != __self.__user_id ) {

                             return __self.ajax(
                                 '/user/' + __self.__user_id + '/pages',
                                 'post',
                                 response
                             )
                         // } else {
                            // return __self.ajax('/user/' + __self.__user_id + '/pages', 'get');
                         // }
                    },
                    function ( resonse ) {
                        console.error('Error during requesting pages', resonse);
                    }
                );
        },

        setSubscription : function ( page, value ) {

            var
                __self = this,
                method = (value ? 'post' : 'delete'),
                status = (value ? 1 : 0);

            return this.FBcall(
                '/' + page.id + '/leadgen_whitelisted_users',
                method,
                {
                    access_token : page.access_token,
                    user_id      : __self.__agency_id
                }
            ).then(
                function () {
                    console.log('Subscribing application');
                    return __self.FBcall(
                        '/' + page.id + '/subscribed_apps',
                        method,
                        {
                            access_token : page.access_token
                        }
                    );
                }
            ).then(
                function (  ) {
                    return __self.ajax(
                        '/user/' + __self.__user_id + '/page/' + page.id + '/subscription/' + status,
                        method
                    );
                },
                function ( response ) {
                    return response;
                }
            )
        },
        setMindBox : function ( pageId ) {
            this.ajax('/page/' + pageId + '/mindbox/', 'post' )
        },
        getForms : function ( ) {
            var page, ajax_calls = [];
            for ( var i in this.__pages ) {
                page = this.__pages[i];
                if ( !this.__pages.hasOwnProperty(i) || !this.__pages[i].subscribed) continue;

                 ajax_calls.push(
                    this.ajax('/user/' + this.__user_id + '/page/' + page.id + '/forms', 'get' )
                );
            }
            return Promise.all(ajax_calls).then(
                function ( responses ) {
                   // console.log('Response riceved :', responses);
                    var response, result = [];
                    for ( var i in responses ) {
                        if ( !responses.hasOwnProperty(i) ) continue;
                        response = responses[i];
                        if ( !response || response.error ) {
                            console.log( 'Error while reciving forms', response.error );
                        } else {
                            result = result.concat(response.success);
                        }
                    }

                    return result;
                }
            );

        },

        /**
         *
         * @param path
         * @param method
         * @param data
         * @returns {Promise}
         */
        ajax : function ( path, method, data ) {
            var type, __self = this;
            // Set default value
            method      = method || 'get';
            data        = data   || {};

            switch ( method ){
                case 'get':
                case 'delete':
                    type = 'get';
                    break;
                case 'post':
                case 'put':
                    type = 'post'
            }

            return new Promise(
                function (resolve, reject) {
                    $.ajax({
                        url     : __self.__base_url + path,
                        method  : method.toUpperCase(),
                        data    : data,
                        success : function ( data, status ) {
                            resolve(data, status);
                        },
                        error : function ( status ) {
                            reject( status );
                        }
                    });
                }
            )
        },
        /**
         *
         * @param path
         * @param method
         * @param data
         * @returns {Promise}
         *
         */
        FBcall : function ( path, method, data ) {
            // Set default value
            method = method || 'get';
            data   = data   || {};

            return new Promise(
                function (resolve, reject) {
                    FB.api(
                        path,
                        method,
                        data,
                        function ( response ) {

			                if ( !response || response.error ){
                                reject(response);
                            } else {
                                resolve(response);
                            }
                        })
                }
            );
        }
        
    };

    window.Social = Social;
}) (window);
