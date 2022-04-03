jQuery(function($) {
    function voca_owerlay(){
        $('body').append('<div class="voca-owerlay"><div class="voca-preloader voca-preloader__white"></div></div>');
    }
    function vocaModal() {
            $('.voca-owerlay .voca-preloader').remove();
            $('body').append('<div class="voca-modal"><div class="voca-modal__inner"><div class="voca-modal__close"></div><div class="voca-modal__content"></div></div></div>');
            $('.voca-modal__close').click(function () {
                $('.voca-modal').remove();
                $('.voca-owerlay').remove();
            })
    }
    $( document ).ready(function() {
        voca_cats();
    })

    function voca_cats(){
        $.ajax({
            url: ajax.url,
            data: {action: 'voca_cats'
            },
            type: 'POST',
            beforeSend: function () {
                $('.voca-cats').html('<div class="voca-preloader"></div>');
            },
            success: function (data) {
                $('.voca-cats').html(data);
                voca__remove_cat();
                voca__add_cat();
                voca_cat_open();
            }
        });
    }

    function voca_cat_open() {
        $('li.voca-cats__item > span').each(function () {
            let slug = $(this).parent().data('slug');
            $(this).click(function () {
                $.ajax({
                    url: ajax.url,
                    data: {
                        action: 'voca__show_items', slug: slug
                    },
                    type: 'POST',
                    beforeSend: function () {
                        $('.voca-cat').html('<div class="voca-preloader"></div>');
                    },
                    success: function (data) {
                        $('.voca-cat').html(data);
                        voca__voice();
                        voca__add_item();
                        voca__remove_item();
                    }
                });
            })

        })
    }

    function voca__add_cat() {
        $('.voca-cats__add').click(function () {
            $.ajax({
                url: ajax.url,
                data: {action: 'voca__add_cat'},
                type: 'POST',
                beforeSend: function () {
                    voca_owerlay();
                },
                success: function (data) {
                    setTimeout(function () {
                        vocaModal();
                        $('.voca-modal__content').html(data);
                        setTimeout(function(){
                            populateVoiceList();
                        }, 100);
                        voca__add_cat_function();
                    }, 10)

                }
            });
        });
    }
    function voca__add_item() {
        $('.voca-items__add').click(function () {
            let itemDataCat = $(this).data('cat');
            $.ajax({
                url: ajax.url,
                data: {action: 'voca__add_item', cat: itemDataCat},
                type: 'POST',
                beforeSend: function (){
                    voca_owerlay();
                },
                success: function (data) {
                    setTimeout(function () {
                        vocaModal();
                        $('.voca-modal__content').html(data);
                        voca__add_item_function();
                    }, 10)
                }
            });
        });
    }

    function voca__add_cat_function(){
        $('.voca-cats__add-form button').click(function(){
            let catTitle = $(this).parent().find('input.voca__cat-title').val();
            let catDesc = $(this).parent().find('textarea.voca__cat-desc').val();
            let catVoice = $(this).parent().find('select#voca-voice option:selected').data('name');
            $.ajax({
                url: ajax.url,
                data: {action: 'voca__add_cat_function', title: catTitle, desc: catDesc, voice: catVoice
                },
                type: 'POST',
                success: function (data) {
                    $('.voca-modal').remove();
                    $('.voca-owerlay').remove();
                    $.ajax({
                        url: ajax.url,
                        data: {action: 'voca_cats'
                        },
                        type: 'POST',
                        beforeSend: function () {
                            $('.voca-cats').html('<div class="voca-preloader"></div>');
                        },
                        success: function (data) {
                            $('.voca-cats').html(data);
                            voca__remove_cat();
                            voca_cat_open();
                        }
                    });
                }
            });
        })
    }
    function voca__add_item_function(){
        $('.voca-items__add-form button').click(function(){
            let itemTitle = $(this).parent().find('input.voca__item-text').val();
            let itemTranslate = $(this).parent().find('input.voca__item-translate').val();
            let itemCat = $(this).data('cat');
            let itemCatSlug = $(this).data('slug');
            $.ajax({
                url: ajax.url,
                data: {action: 'voca__add_item_function', text: itemTitle, translate: itemTranslate, cat: itemCat
                },
                type: 'POST',
                success: function (data) {
                    // $('.voca-modal__content').html('Item was successfully added');
                    $('.voca-modal').remove();
                    $('.voca-owerlay').remove();
                    $.ajax({
                        url: ajax.url,
                        data: {action: 'voca__show_items', slug: itemCatSlug,
                        },
                        type: 'POST',
                        beforeSend: function () {
                            $('.voca-cat').html('<div class="voca-preloader"></div>');
                        },
                        success: function (data) {
                            $('.voca-cat').html(data);
                            voca__voice();
                            voca__add_item();
                            voca__remove_item();
                        }
                    });
                }
            });
        })
    }
    function voca__remove_cat(){
        $('.voca-cats ul li').each(function(){
            $(this).find('.voca-item__remove').click(function(){
                let itemId = $(this).parents('li.voca-cats__item').data('id');
                $.ajax({
                    url: ajax.url,
                    data: {action: 'voca__remove_cat_function', id: itemId},
                    type: 'POST',
                    success: function (data) {
                        voca_cats();s
                        if($('.voca-cat .voca-items__add').data('cat') === itemId){
                            $('.voca-cat').html('');
                        }
                    }
                });

            })
        })
    }

    function voca__remove_item(){
        $('.voca-cat .voca-item').each(function(){
            $(this).find('.voca-item__remove').click(function(){
                let itemId = $(this).parents('.voca-item').data('id');
                let itemCatSlug = $(this).parents('.voca-item').data('slug');
                console.log(itemId);
                $.ajax({
                    url: ajax.url,
                    data: {action: 'voca__remove_item_function', id: itemId},
                    type: 'POST',
                    beforeSend: function () {
                        $('.voca-cat').html('<div class="voca-preloader"></div>');
                    },
                    success: function (data) {
                        $.ajax({
                            url: ajax.url,
                            data: {action: 'voca__show_items', slug: itemCatSlug,
                            },
                            type: 'POST',
                            beforeSend: function () {
                                $('.voca-cat').html('<div class="voca-preloader"></div>');
                            },
                            success: function (data) {
                                $('.voca-cat').html(data);
                                voca__voice();
                                voca__add_item();
                                voca__remove_item();
                            }
                        });
                    }
                });
            })
        })
    }
    const synth = window.speechSynthesis;
    function populateVoiceList() {
        const voicesList = document.querySelector('select#voca-voice');
        voices = synth.getVoices();
        const selectedIndex =
            voicesList.selectedIndex < 0 ? 0 : voicesList.selectedIndex;
        voicesList.innerHTML = '';
        for (i = 0; i < voices.length; i++) {
            const option = document.createElement('option');
            option.textContent = voices[i].name + ' (' + voices[i].lang + ')';

            if (voices[i].default) {
                option.textContent += ' -- DEFAULT';
            }

            option.setAttribute('data-lang', voices[i].lang);
            option.setAttribute('data-name', voices[i].name);
            voicesList.appendChild(option);
        }
        voicesList.selectedIndex = selectedIndex;
    }
    function voca__voice() {
        $('.voca-item__play').each(function () {
            $(this).click(function () {
                if (synth.speaking) {
                    synth.cancel();
                    setTimeout(speak, 300);
                } else {
                    let utterThis = new SpeechSynthesisUtterance($(this).next().html());
                    voices = synth.getVoices();
                    let selectedOption = $(this).parents('.voca-items').data('voice');
                    for (i = 0; i < voices.length; i++) {
                        if (voices[i].name === selectedOption) {
                            utterThis.voice = voices[i];
                        }
                    }
                    synth.speak(utterThis);
                }
            })
        })
    }
})