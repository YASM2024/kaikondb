// nextPageLoader.js
class NextPageLoader {
    constructor(data) {
        function pages(c, n) {
            if (n < 6) {
                return [...Array(n)].map((_, i) => i + 1);
            } else if (c < 5) {
                return [1, 2, 3, 4, 5, 0, n];
            } else if (c > n - 4) {
                return [1, 0, n - 4, n - 3, n - 2, n - 1, n];
            }
            return [1, 0, c - 1, c, c + 1, 0, n];
        }

        this.last_page = data['last_page'];
        this.current_page = data['current_page']; 
        this.per_page = data['per_page']; 
        this.total = data['total']; 
        this.eleBtn = document.getElementById('next_page_loader');
        this.eleMsg = document.getElementById('number_of_show');
    }

    printBtn() {
        if (this.last_page == 1 | this.current_page == this.last_page) {
            this.eleBtn.innerHTML = '';
            return false;
        }
        let html = '<div class="next_records border w-100 my-2 p-2 bg-white text-center cursor-pointer" id="next_page_btn" data-current-page="' + this.current_page + '">';
        html += '続きを表示する';
        html += '</div>';
        this.eleBtn.innerHTML = html;        
        return true;
    }

    printMsg() {
        if (this.current_page == this.last_page) {
            this.eleMsg.innerText = 'ページの終わりです。 Showing ' + this.total + ' of ' + this.total;
        }else{
            this.eleMsg.innerText = ' Showing ' + Math.min(this.total, (this.per_page * this.current_page)) + ' of ' + this.total;
        }
    }
}

// window.NextPageLoader = NextPageLoader;
