// title      : jSCAD Logo
// author     : unix-world.org (based on a logo by Rene K. Mueller)
// license    : BSD
// revision   : 1.0
// tags       : Logo,Intersection,Sphere,Cube
// file       : logo.jscad

function main() {
   return union(
      difference(
         color('navy', cube({size: 3, center: true})),
         color('navy', sphere({r: 2, center: true}))
      ),
      color('yellow', intersection(
          sphere({r: 1.5, center: true}),
          cube({size: 2.2, center: true})
      )),
   ).translate([0,0,1.5]).scale(10);
}
