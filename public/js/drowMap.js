async function drowMap(dist_str) {
    const dist_array = dist_str.split(';');
    
    // SVG外部ファイルを読み込む
    const response = await fetch('./maps/19_yamanashi.svg');
    const text = await response.text();
    const parser = new DOMParser();
    const svgDoc = parser.parseFromString(text, "image/svg+xml");
    
    const shapeGroup = svgDoc.getElementById('yamanashi-map-shapes');
    
    // 動的スタイル構築
    let style = '<style>.map {stroke:black;stroke-width:4;stroke-miterlimit:22.9256;fill:#eeeeee;width:100%;height:100%;}\n';
    dist_array.forEach(function(dist_code) {
        style += `#c${dist_code} { fill:#4db56a; }\n`;
    });
    style += '</style>';

    // SVG全体を構築
    let svg_source = '';
    svg_source += style;
    svg_source += `<svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" viewBox="0 0 1200 1200">`;
    svg_source += new XMLSerializer().serializeToString(shapeGroup);

    // 特定地域の円とテキスト表示
    if (dist_array.includes('199900')) {
        svg_source += '<circle class="map" id="c199900" cx="11000" cy="18080" r="600" />';
        svg_source += '<text class="map" id="c199900" x="12000" y="18500" font-family="gothic" font-weight="bold" font-size="1200">不明</text>';
    }

    svg_source += '</svg>';
    
    return svg_source;
}
