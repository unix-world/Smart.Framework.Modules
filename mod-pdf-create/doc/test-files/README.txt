zFPDF accepts UTF-8 encoded text. It embeds font subsets allowing small PDF files.

It requires a folder 'unifont' as the 'font' folder.

All zFPDF requires is a .ttf TrueType font file. The file should be placed in the
'unifont' directory. 

Pass a fourth parameter as true when calling AddFont(), and use utf-8 encoded text 
when using Write() etc.

