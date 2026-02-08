export const DOM = {
    httpquery: document.getElementById('httpquery'),
    app: document.getElementById('app'),
    userIdSearchEle: document.getElementById('user_id_selectbox'),
    ModalLabel: document.getElementById('ModalLabel'),
    photoModal: document.getElementById('photoModal'),
    profileModal: document.getElementById('profileModal'),
    btnSearch: document.getElementById('btnSearch'),
    btnClear: document.getElementById('btnClear'),
    viewDataTitle: document.querySelector('.view_data[name="title"]'),
    viewDataPlace: document.querySelector('.view_data[name="place"]'),
    viewDataDate: document.querySelector('.view_data[name="date"]'),
    viewDataPhotographer: document.querySelector('.view_data[name="photographer"]'),
    viewDataMemo: document.querySelector('.view_data[name="memo"]'),
    photo_url: document.getElementById('photo_url'),
};
export let DOM_auth
if(window.authenticated) {
    DOM_auth = {
        // レコードの編集/削除
        agreeBtn: document.getElementById('agreementButton'),
        postBtn: document.getElementById('postButton'),
        editBtn: document.getElementById('editBtn'),
        delBtn: document.getElementById('delBtn'),

        // 新規作成
        photoRegisterModal: document.getElementById('photoRegisterModal'),
        new_photo_title_Ele: document.getElementById('new_photo_title'),
        new_place_Ele: document.getElementById('new_place'),
        new_date_Ele: document.getElementById('new_date'),
        new_memo_Ele: document.getElementById('new_memo'),
        new_image_file_Ele: document.getElementById('new_image_file'),
        createSubmitBtn: document.getElementById('create_submit'),
        photoEditModal: document.getElementById('photoEditModal'),
    };
}