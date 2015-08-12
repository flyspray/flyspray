# Sparkline

PHP script to generate sparklines, with browser cachine with ETag.

## Usage
    
```html
<img src='sparkline.php?size=80x20&data=2,4,5,6,10,7,8,5,7,7,11,8,6,9,11,9,13,14,12,16&back=fff&line=5bb763&fill=d5f7d8' />
```

## Examples

<img src='http://jamiebicknell.github.io/Sparkline/sparkline_1403094493691.png' alt='EG1' /><br />
`sparkline.php`

<img src='http://jamiebicknell.github.io/Sparkline/sparkline_1403094493692.png' alt='EG2' /><br />
`sparkline.php?data=5`

<img src='http://jamiebicknell.github.io/Sparkline/sparkline_1403094493693.png' alt='EG3' /><br />
`sparkline.php?data=2,4,5,6,10,7,8,5,7,7,11,8,6,9,11,9,13,14,12,16`

<img src='http://jamiebicknell.github.io/Sparkline/sparkline_1403094493694.png' alt='EG4' /><br />
`sparkline.php?data=2,4,5,6,10,7,8,5,7,7,11,8,6,9,11,9,13,14,12,16&line=5bb763&fill=d5f7d8`

<img src='http://jamiebicknell.github.io/Sparkline/sparkline_1403094493695.png' alt='EG5' /><br />
`sparkline.php?data=2,4,5,6,10,7,8,5,7,7,11,8,6,9,11,9,13,14,12,16&line=fd8626&fill=ffedde`

<img src='http://jamiebicknell.github.io/Sparkline/sparkline_1403094493696.png' alt='EG6' /><br />
`sparkline.php?data=2,4,5,6,10,7,8,5,7,7,11,8,6,9,11,9,13,14,12,16&line=ed5565&fill=ffe2e2`

<img src='http://jamiebicknell.github.io/Sparkline/sparkline_1403094493697.png' alt='EG7' /><br />
`sparkline.php?data=2,4,5,6,10,7,8,5,7,7,11,8,6,9,11,9,13,14,12,16&line=444&fill=eee`

<img src='http://jamiebicknell.github.io/Sparkline/sparkline_1403094493698.png' alt='EG8' /><br />
`sparkline.php?data=2,4,5,6,10,7,8,5,7,7,11,8,6,9,11,9,13,14,12,16&line=31475c&fill=fff`

<img src='http://jamiebicknell.github.io/Sparkline/sparkline_1403094493699.png' alt='EG9' /><br />
`sparkline.php?size=185x40&data=2,4,5,6,10,7,8,5,7,7,11,8,6,9,11,9,13,14,12,16`


## Query Parameters

<table>
    <tr>
        <th>Key</th>
        <th>Example Value</th>
        <th>Default</th>
        <th>Description</th>
    </tr>
    <tr>
        <td>size</td>
        <td>100x25, 100</td>
        <td>80x20</td>
        <td>Width must be between 50 and 80<br />Height must be between 20 and 800</td>
    </tr>
    <tr>
        <td>data</td>
        <td>10,20,50,20,30,40,50,120,90</td>
        <td></td>
        <td>Comma separated list of values to plot</td>
    </tr>
    <tr>
        <td>back</td>
        <td>eeeeee, ddd</td>
        <td>ffffff</td>
        <td>Hexadecimal code for background colour</td>
    </tr>
    <tr>
        <td>line</td>
        <td>555555, 222</td>
        <td>1388db</td>
        <td>Hexadecimal code for line colour</td>
    </tr>
    <tr>
        <td>fill</td>
        <td>cccccc, bbb</td>
        <td>e6f2fa</td>
        <td>Hexadecimal code for fill colour</td>
    </tr>
</table>

## Size Parameter

<table>
    <tr>
        <th>Value</th>
        <th>Description</th>
    </tr>
    <tr>
        <td>100</td>
        <td>Creates a square image 100px in width and 100px in height</td>
    </tr>
    <tr>
        <td>80x20</td>
        <td>Creates an image 80px in width and 20px in height</td>
    </tr>
</table>

##License

Sparkline is licensed under the [MIT license](http://opensource.org/licenses/MIT), see [LICENSE.md](https://github.com/jamiebicknell/Sparkline/blob/master/LICENSE.md) for details.