Description

This script is a reusable system for adding a graphical keyboard interface to text fields, password fields and textareas so they can be filled with mouse only. It also adds easy access to special characters your existing keyboard may not otherwise have the ability to generate. Comes with Arabic, Armenian East/West, Belarusian, Belgian, Bengali, Bulgarian Phonetic, Burmese, Czech, Danish, Dutch, Dvorak, Farsi (Persian), French, German, Greek, Hebrew, Hindi, Hungarian, Italian, Japanese Hiragana/Katakana (Basic), Kazakh, Lithuanian, Macedonian, Norwegian, Number Pad, Pashto, Pinyin, Polish Programmers, Portuguese, Romanian, Russian, Serbian Cyrillic, Serbian Latin, Slovak, Slovenian, Spanish (Spain), Swedish, Turkish-F, Turkish-QWERTY, UK, Ukrainian, US Standard and US International keyboard layouts, dynamically selectable. Plus it's easy to add other layouts!

Compared to other javascript virtual keyboards, this main benefit of this script is that it is extremely size-efficient. Including all available keyboard layouts, the script, CSS and single keyboard image is less than 100kB, ensuring that it won't become a drain on your server.
Installation

To activate this script within an HTML document: First include the external JavaScript file, "keyboard.js" and stylesheet, "keyboard.css" within the document's <head> element (download links to the right).

<script type="text/javascript" src="keyboard.js" charset="UTF-8"></script>
<link rel="stylesheet" type="text/css" href="keyboard.css">

Then, to enable a graphical keyboard interface on any particular text field or textarea, simply apply to it the keyboardInput class, like so:

<input type="text" value="" class="keyboardInput">

Then, when your document loads, the script will find all elements labeled with this class and automatically insert the keyboard link. Make sure that the keyboard.png image is in the right location for it to be loaded by the script! If JavaScript is disabled, the keyboard icons simply do not appear, so the script degrades gracefully. You may safely remove the keyboardInput class via scripting after the document has loaded.

This script has been tested to work in IE6, IE7, IE8 and the latest versions of: Firefox, Opera, Safari for Windows and Chrome. There is a known limitation in the Firefox browser where if you have an input within a position:fixed; container with a percentage (%) width, the script will replace this with an absolute (px) value; ergo screen resizes will no longer vary the width of the container. The dynamic keyboard positioning for each text field probably means this script will not work properly on pages triggering quirks mode in any browser... but maybe it will work.
Customisation

Default keyboard layout

	To change the default keyboard which displays first for each different page, change the value of the this.VKI_kt variable to the name of the keyboard. For example, to make the default keyboard "Greek", change the value like so: this.VKI_kt = "Greek";.
Dead keys

	To turn dead keys on by default, set the value of this.VKI_deadkeysOn to true.
Clear passwords

	To have the keyboard automatically clear password inputs when it is invoked, set the value of this.VKI_clearPasswords to true. This is mainly useful when there are characters already in the field; since users don't know what they are, it's likely they would like to start their input over.
Hide version number

	To disable display of the version number in the bottom right hand corner, set the value of this.VKI_showVersion to false.
Clickless interface

	To enable the clickless interface option, set the value of this.VKI_clickless to an integer greater than 0. This interface mode allows you to enter characters simply by hovering the mouse over them for a short time, which can provide an extra layer of protection from sophisticated spyware which may take screenshots on mouse-click. The value of the this.VKI_clickless variable is the amount of hover time (in milliseconds) required to activate a key. (1000 milliseconds = 1 second)
Keyboard size control

	The keyboard includes a control that allows the user to adjust the size of the keyboard. Five sizes based on font-size have been pre-programmed: 9px, 11px (default), 13px, 16px and 20px; corresponding to the sizes 1 to 5 respectively. To disable the appearance of this dropdown control, set the value of this.VKI_sizeAdj to false. The value of this.VKI_size is the default size to be used by the keyboard when it first appears on a page.

	To change the size of the keyboard to values other than those listed above, just change the font size in the #keyboardInputMaster * { ... } rules in the associated CSS.
Automatic layout selection

	By default, the keyboard will use the lang attribute of each enabled form element in order to determine which keyboard to display. For example, if a text input contains the attribute lang="fr" then the French keyboard layout will be automatically selected by default when the keyboard is invoked. If no lang attribute exists, or a compatible keyboard layout is not found, then the default or currently selected layout will be displayed. To disable this behaviour, set the value of this.VKI_langAdapt to false.

	The script uses valid two letter language codes with optional subtags, as defined in RFC 1766. You can find out which codes will trigger particular keyboards by examining the .lang property of each keyboard layout as defined in the source code.

Advanced Stuff

The script exposes the event attachment function to scripting via the global VKI_attach function. If your document creates inputs after the page has loaded, whether through Ajax processes or user interaction, you can attach the keyboard events to them by passing the element to VKI_attach. For example:

var foo = document.createElement('input');
document.body.appendChild(foo);
VKI_attach(foo);

You can only attach keyboard events to elements which have already been added to the document. Once the script has attached the keyboard events to an input or textarea element, it will set the element's .VKI_attached property to true. You can check for this property in your scripts to prevent applying the events to an input element which already has them, like so:

var myInput = document.getElementById('myInput');
if (!myInput.VKI_attached) VKI_attach(myInput);

The script also exposes the keyboard close function via the global VKI_close function. Other scripts on your pages may call this function to close the keyboard if, for example, an element which contains activated keyboard inputs is hidden by the user.

function closeDialogue() {
  document.getElementById('myDialogue').style.display = "none";
  VKI_close();
}

// - Lay out each keyboard in rows of sub-arrays.  Each sub-array
//   represents one key.
//
// - Each sub-array consists of four slots described as follows:
//     example: ["a", "A", "\u00e1", "\u00c1"]
//
//          a) Normal character
//          A) Character + Shift/Caps
//     \u00e1) Character + Alt/AltGr/AltLk
//     \u00c1) Character + Shift/Caps + Alt/AltGr/AltLk
//
//   You may include sub-arrays which are fewer than four slots.
//   In these cases, the missing slots will be blanked when the
//   corresponding modifier key (Shift or AltGr) is pressed.
//
// - If the second slot of a sub-array matches one of the following
//   strings:
//     "Tab", "Caps", "Shift", "Enter", "Bksp",
//     "Alt" OR "AltGr", "AltLk"
//   then the function of the key will be the following,
//   respectively:
//     - Insert a tab
//     - Toggle Caps Lock (technically a Shift Lock)
//     - Next entered character will be the shifted character
//     - Insert a newline (textarea), or close the keyboard
//     - Delete the previous character
//     - Next entered character will be the alternate character
//     - Toggle Alt/AltGr Lock
//
//   The first slot of this sub-array will be the text to display
//   on the corresponding key.  This allows for easy localisation
//   of key names.
//
// - Layout dead keys (diacritic + letter) should be added as
//   arrays of two item arrays with hash keys equal to the
//   diacritic.  See the "this.VKI_deadkey" object below the layout
//   definitions.  In  each two item child array, the second item
//   is what the diacritic would change the first item to.
//
// - To disable dead keys for a layout, simply assign true to the
//   DDK property of the layout (DDK = disable dead keys).  See the
//   Numpad layout below for an example.
//
// - Note that any characters beyond the normal ASCII set should be
//   entered in escaped Unicode format.  (eg \u00a3 = Pound symbol)
//   You can find Unicode values for characters here:
//     http://unicode.org/charts/
//
// - To remove a keyboard, just delete it, or comment it out of the
//   source code. If you decide to remove the US Int'l keyboard
//   layout, make sure you change the default layout (this.VKI_kt)
//   above so it references an existing layout.

# END
