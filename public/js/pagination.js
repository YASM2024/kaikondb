function Pagination(data){
    function pages(c, n) {
        if (n < 6) {
            return [...Array(n)].map((_, i) => i + 1);
        }
        else if (c < 5) {
            return [1, 2, 3, 4, 5, 0, n];
        }
        else if (c > n - 4) {
            return [1, 0, n - 4, n - 3, n - 2, n - 1, n];
        }
            return [1, 0, c - 1, c, c + 1, 0, n];
    }
    this.last_page = data['last_page'];
    this.current_page = data['current_page']; 
    this.per_page = data['per_page']; 
    this.total = data['total']; 
    this.eleLink = document.getElementById('pagination');
    this.eleMsg = document.getElementById('number_of_show');
    this.printLink = function(){
        if(this.last_page == 1){
            this.eleLink.innerHTML = '';
            return false;
        }
        var html = '<ul class="pagination mt-3">';
        html += pages(this.current_page, this.last_page)
        .map((num) => {
        if (num === this.current_page) {
            return `<li class="page-item mx-1"><a class="page-link bg-secondary text-light">${num}</a></li>`;
        }
        if (num) {
            return `<li class="page-item mx-1"><a class="page-link pe-auto" onclick="searchPage(${num});" role="button">${num}</a></li>`;
        }
        return '<li class="page-item mx-1"><a class="page-link">...</a></li>';
        })
        .join('');
        html += '</ul>';
        this.eleLink.innerHTML = html;        
        return true;
    }
    this.printMsg = function(){
        this.eleMsg.innerText = ' Showing ' + ((this.per_page * (this.current_page - 1)) + 1) + ' to ' + Math.min( this.total, (this.per_page * this.current_page)) + ' of ' + this.total;
    }
}