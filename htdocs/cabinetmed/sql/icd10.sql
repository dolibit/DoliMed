-- ICD-10 Version 2019 icd.who.int 

-- A -> 1 , B -> 2 ..


-- llx_cabinetmed_diaglec 
-- Chapter
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 01  , 'A00-B99', 'Chapter I'     , 'Certain infectious and parasitic diseases'); 
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 02  , 'C00-D48', 'Chapter II'    , 'Neoplasms'); 
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 03  , 'D50-D89', 'Chapter III'   , 'Diseases of the blood and blood-forming organs and certain disorders involving the immune mechanism'); 
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 04  , 'E00-E90', 'Chapter IV'    , 'Endocrine, nutritional and metabolic diseases'); 
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 05  , 'F00-F99', 'Chapter V'     , 'Mental and behavioural disorders'); 
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 06  , 'G00-G99', 'Chapter VI'    , 'Diseases of the nervous system'); 
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 07  , 'H00-H59', 'Chapter VII'   , 'Diseases of the eye and adnexa'); 
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 08  , 'H60-H95', 'Chapter VIII'  , 'Diseases of the ear and mastoid process'); 
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 09  , 'I00-I99', 'Chapter IX'    , 'Diseases of the circulatory system'); 
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 10  , 'J00-J99', 'Chapter X'     , 'Diseases of the respiratory system'); 
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 11  , 'K00-K93', 'Chapter XI'    , 'Diseases of the digestive system');
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 12  , 'L00-L99', 'Chapter XII'   , 'Diseases of the skin and subcutaneous tissue');
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 13  , 'M00-M99', 'Chapter XIII'  , 'Diseases of the musculoskeletal system and connective tissue');
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 14  , 'N00-N99', 'Chapter XIV'   , 'Diseases of the genitourinary system');
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 15  , 'O00-O99', 'Chapter XV'    , 'Pregnancy, childbirth and the puerperium');
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 16  , 'P00-P96', 'Chapter XVI'   , 'Certain conditions originating in the perinatal period');
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 17  , 'Q00-Q99', 'Chapter XVII'  , 'Congenital malformations, deformations and chromosomal abnormalities');
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 18  , 'R00-R99', 'Chapter XVIII' , 'Symptoms, signs and abnormal clinical and laboratory findings, not elsewhere classified');
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 19  , 'S00-T98', 'Chapter XIX'   , 'Injury, poisoning and certain other consequences of external causes');
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 20  , 'V01-Y98', 'Chapter XX'    , 'External causes of morbidity and mortality');
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 21  , 'Z00-Z99', 'Chapter XXI'   , 'Factors influencing health status and contact with health services');
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 22  , 'U00-U85', 'Chapter XXII'  , 'Codes for special purposes');


-- CHAPTER I                                                 VALUES ( 01   , 'A00-B99', 'Chapter I' , 'Certain infectious and parasitic diseases'); 
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 010  , 'A00-A09', 'A00-A09', 'Intestinal infectious diseases'); 
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 0100 , 'A00'    , 'A00'    , 'Cholera'); 
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 01000, 'A00.0'  , 'A00.0'  , 'Cholera due to Vibrio cholerae 01, biovar cholerae'); 
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 01001, 'A00.1'  , 'A00.1'  , 'Cholera due to Vibrio cholerae 01, biovar eltor'); 
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 01009, 'A00.9'  , 'A00.9'  , 'Cholera, unspecified'); 
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 0101 , 'A01'    , 'A01'    , 'Typhoid and paratyphoid fevers'); 
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 0102 , 'A02'    , 'A02'    , 'Other salmonella infections'); 
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 0103 , 'A03'    , 'A03'    , 'Shigellosis'); 
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 0104 , 'A04'    , 'A04'    , 'Other bacterial intestinal infections'); 
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 0105 , 'A05'    , 'A05'    , 'Other bacterial foodborne intoxications, not elsewhere classified'); 
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 0106 , 'A06'    , 'A06'    , 'Amoebiasis'); 
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 0107 , 'A07'    , 'A07'    , 'Other protozoal intestinal diseases'); 
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 0108 , 'A08'    , 'A08'    , 'Viral and other specified intestinal infections'); 
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 0109 , 'A09'    , 'A09'    , 'Other gastroenteritis and colitis of infectious and unspecified origin'); 

INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 015  , 'A15-A19', 'A15-A19', 'Tuberculosis'); 
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 0150 , 'A15'    , 'A15'    , 'Respiratory tuberculosis, bacteriologically and histologically confirmed'); 
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 0160 , 'A16'    , 'A16'    , 'Respiratory tuberculosis, not confirmed bacteriologically or histologically');
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 0170 , 'A17'    , 'A17'    , 'Tuberculosis of nervous system');
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 0180 , 'A18'    , 'A18'    , 'Tuberculosis of other organs');

INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 020  , 'A20-A28', 'A20-A28', 'Certain zoonotic bacterial diseases'); 
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 0200 , 'A20'    , 'A20'    , 'Plague'); 
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 0210 , 'A21'    , 'A21'    , 'Tularaemia'); 
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 0220 , 'A22'    , 'A22'    , 'Anthrax');
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 0230 , 'A23'    , 'A23'    , 'Brucellosis');
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 0240 , 'A24'    , 'A24'    , 'Glanders and melioidosis');
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 0250 , 'A25'    , 'A25'    , 'Rat-bite fevers');
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 0260 , 'A26'    , 'A26'    , 'Erysipeloid');
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 0270 , 'A27'    , 'A27'    , 'Leptospirosis');
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 0280 , 'A28'    , 'A28'    , 'Other zoonotic bacterial diseases, not elsewhere classified');

INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 030  , 'A30-A49', 'A30-A49', 'Other bacterial diseases'); 

INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 050  , 'A50-A64', 'A50-A64', 'Infections with a predominantly sexual mode of transmission'); 

INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 065  , 'A65-A69', 'A65-A69', 'Other spirochaetal diseases'); 

INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 070  , 'A70-A74', 'A70-A74', 'Other diseases caused by chlamydiae'); 

INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 075  , 'A75-A79', 'A75-A79', 'Rickettsioses'); 

INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 080  , 'A80-A89', 'A80-A89', 'Viral infections of the central nervous system'); 

INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 090  , 'A92-A99', 'A92-A99', 'Arthropod-borne viral fevers and viral haemorrhagic fevers'); 

-- B
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 100  , 'B00-B09', 'B00-B09', 'Viral infections characterized by skin and mucous membrane lesions'); 

-- Chapter II 


-- Chapter III 


-- Chapter IV  

-- INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES (  1, U99.9!, 'Other', 'Other'); 
-- INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES (  1, U99.9!, 'Other', 'Other'); 
INSERT INTO llx_cabinetmed_diaglec (rowid, icd, code, label) VALUES ( 2209, 'U99.9!', 'Other', 'Other'); 
