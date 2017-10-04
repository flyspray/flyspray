Calendar._DN = new Array
("Κυριακή",
 "Δευτέρα",
 "Τρίτη",
 "Τετάρτη",
 "Πέμπτη",
 "Παρασκευή",
 "Σάββατο",
 "Κυριακή");

Calendar._SDN = new Array
("Κυ",
 "Δε",
 "Tρ",
 "Τε",
 "Πε",
 "Πα",
 "Σα",
 "Κυ");

Calendar._FD = 1;

Calendar._MN = new Array
("Ιανουάριος",
 "Φεβρουάριος",
 "Μάρτιος",
 "Απρίλιος",
 "Μάϊος",
 "Ιούνιος",
 "Ιούλιος",
 "Αύγουστος",
 "Σεπτέμβριος",
 "Οκτώβριος",
 "Νοέμβριος",
 "Δεκέμβριος");

Calendar._SMN = new Array
("Ιαν",
 "Φεβ",
 "Μαρ",
 "Απρ",
 "Μαι",
 "Ιουν",
 "Ιουλ",
 "Αυγ",
 "Σεπ",
 "Οκτ",
 "Νοε",
 "Δεκ");

// tooltips
Calendar._TT = {};
Calendar._TT["INFO"] = "Σχετικά με το ημερολόγιο";

Calendar._TT["ABOUT"] =
"Επιλογέας ημερομηνίας/ώρας σε DHTML\n" +
"(c) dynarch.com 2002-2005 / Author: Mihai Bazon\n" + // don't translate this this ;-)
"Για τελευταία έκδοση: http://www.dynarch.com/projects/calendar/\n" +
"Διανέμεται υπό την GNU LGPL.  Βλ. http://gnu.org/licenses/lgpl.html για λεπτομέρειες." +
"\n\n" +
"Επιλογή ημερομηνίας:\n" +
"- Χρησιμοποιήστε τα κουμπιά \xab, \xbb για επιλογή έτους\n" +
"- Χρησιμοποιήστε τα κουμπιά " + String.fromCharCode(0x2039) + ", " + String.fromCharCode(0x203a) + " για επιλογή μήνα\n" +
"- Κρατήστε το πλήκτρο του ποντικού πατημένο στα παραπάνω κουμπιά για ταχύτερη επιλογή.";
Calendar._TT["ABOUT_TIME"] = "\n\n" +
"Επιλογή ώρας:\n" +
"- Κάντε κλικ σε ένα από τα μέρη της ώρας για αύξηση\n" +
"- ή Shift-κλικ για μείωση\n" +
"- ή κλικ και σύρσιμο για ταχύτερη επιλογή.";
Calendar._TT["TOGGLE"] = "Μπάρα πρώτης ημέρας της εβδομάδας";
Calendar._TT["PREV_YEAR"] = "Προηγ. έτος (παρατεταμένα για μενού)";
Calendar._TT["PREV_MONTH"] = "Προηγ. μήνας (παρατεταμένα για μενού)";
Calendar._TT["GO_TODAY"] = "Σήμερα";
Calendar._TT["NEXT_MONTH"] = "Επόμενος μήνας (παρατεταμένα για μενού)";
Calendar._TT["NEXT_YEAR"] = "Επόμενο έτος (παρατεταμένα για μενού)";
Calendar._TT["SEL_DATE"] = "Επιλέξτε ημερομηνία";
Calendar._TT["DRAG_TO_MOVE"] = "Σύρτε για να μετακινήσετε";
Calendar._TT["PART_TODAY"] = " (σήμερα)";
Calendar._TT["MON_FIRST"] = "Εμφάνιση Δευτέρας πρώτα";
Calendar._TT["SUN_FIRST"] = "Εμφάνιση Κυριακής πρώτα";

// the following is to inform that "%s" is to be the first day of week
// %s will be replaced with the day name.
Calendar._TT["DAY_FIRST"] = "Εμφάνιση %s πρώτα";

// This may be locale-dependent. It specifies the week-end days, as an array
// of comma-separated numbers. The numbers are from 0 to 6: 0 means Sunday, 1
// means Monday, etc.
Calendar._TT["WEEKEND"] = "0,6";

Calendar._TT["CLOSE"] = "Κλείσιμο";
Calendar._TT["TODAY"] = "Σήμερα";
Calendar._TT["TIME_PART"] = "(Shift-)κλικ ή μετακίνηση για αλλαγή";

// date formats
Calendar._TT["DEF_DATE_FORMAT"] = "dd-mm-y";
Calendar._TT["TT_DATE_FORMAT"] = "D, d M";

Calendar._TT["WK"] = "εβδ";

