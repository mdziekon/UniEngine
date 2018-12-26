<script  type="text/javascript">
v  = new Date();
p  = 0;
g  = {b_hangar_id_plus};
s  = 0;
hs = 0;
of = 1;
c  = new Array({c},'');
b  = new Array({b});
a  = new Array({a},'');
aa = '{completed}';

b_length = b.length - 1;

function t() {
    if ( hs == 0 ) {
        xd();
        hs = 1;
    }
    n = new Date();
    s = Math.round(c[p] - g - Math.round((n.getTime() - v.getTime()) / 1000));
    m = 0;
    h = 0;
    if ( s < 0 ) {
        a[p]--;
        xd();
        if ( a[p] <= 0 ) {
            p++;
            xd();
        }
        g = 0;
        v = new Date();
        s = 0;
    }
    if ( s > 59 ) {
        m = Math.floor(s / 60);
        s = s - m * 60;
    }
    if ( m > 59 ) {
        h = Math.floor(m / 60);
        m = m - h * 60;
    }
    if ( s < 10 ) {
        s = "0" + s;
    }
    if ( m < 10 ) {
      m = "0" + m;
    }
    if ( p > b_length ) {
        document.getElementById("bx").innerHTML=aa ;
    } else {
        document.getElementById("bx").innerHTML=b[p]+" "+h+":"+m+":"+s;
    }
    window.setTimeout("t();", 200);
}

function xd() {
    var Element = document.getElementById('auftr');
    while (Element.length > 0) {
        Element.options[Element.length - 1] = null;
        Element.options[Element.length - 1] = null;
    }
    if ( p > b_length ) {
        Element.options[Element.length] = new Option(aa);
    }
    for ( iv = p; iv <= b_length; iv++ ) {
        if ( a[iv] < 2 ) {
            ae = " ";
        } else {
            ae = " ";
        }
        if ( iv == p ) {
            act = " ({in_working})";
        } else {
            act = "";
        }
        Element.options[Element.length] = new Option( a[iv] + ae + " \"" + b[iv] + "\"" + act, iv + of );
    }
}

window.onload = t;
</script>
<br />
<table width="600">
    <tr>
        <td class="c" >{work_todo}</td>
    </tr>
    <tr>
        <th class="pad5">{ActualProduction}: <div id="bx" class="z"></div></th>
    </tr>
    <tr>
        <th><select id="auftr" name="auftr" size="7"></select></th>
    </tr>
    <tr>
        <th class="pad5">{total_left_time} {pretty_time_b_hangar}</th>
    </tr>
</table>
