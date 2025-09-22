-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:4306
-- Generation Time: Sep 22, 2025 at 09:07 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kamala_college`
--

-- --------------------------------------------------------

--
-- Table structure for table `admts`
--

CREATE TABLE `admts` (
  `r_id` int(4) NOT NULL,
  `prn_no` int(11) DEFAULT NULL,
  `r_stu_admi_cls` varchar(100) DEFAULT NULL,
  `r_stu_tit` varchar(10) DEFAULT NULL,
  `r_stu_mother` varchar(100) DEFAULT NULL,
  `r_stu_gen` enum('Male','Female','Other') DEFAULT NULL,
  `r_stu_id` varchar(50) DEFAULT NULL,
  `r_stu_sig` varchar(255) DEFAULT NULL,
  `r_stu_name` varchar(100) DEFAULT NULL,
  `r_stu_father` varchar(100) DEFAULT NULL,
  `r_stu_sur` varchar(100) DEFAULT NULL,
  `r_p_add` text DEFAULT NULL,
  `r_stu_vil` varchar(100) DEFAULT NULL,
  `r_sub_dist` varchar(100) DEFAULT NULL,
  `r_dist` varchar(100) DEFAULT NULL,
  `r_r_add` text DEFAULT NULL,
  `r_stu_ph` varchar(15) DEFAULT NULL,
  `r_stu_G_ph` varchar(15) DEFAULT NULL,
  `r_stu_B` varchar(100) DEFAULT NULL,
  `r_stu_B_sub_dist` varchar(100) DEFAULT NULL,
  `r_stu_B_dist` varchar(100) DEFAULT NULL,
  `r_stu_B_city` varchar(100) DEFAULT NULL,
  `r_stu_B_sta` varchar(100) DEFAULT NULL,
  `r_stu_B_date` date DEFAULT NULL,
  `r_stu_B_dateW` varchar(50) DEFAULT NULL,
  `r_stu_age` int(11) DEFAULT NULL,
  `r_stu_disb` varchar(100) DEFAULT NULL,
  `r_stu_mari` enum('Single','Married','Other') DEFAULT NULL,
  `r_stu_reli` varchar(50) DEFAULT NULL,
  `r_stu_cast` varchar(50) DEFAULT NULL,
  `r_stu_castcat` varchar(50) DEFAULT NULL,
  `r_stu_aadr` varchar(14) DEFAULT NULL,
  `r_stu_pan` char(10) DEFAULT NULL,
  `r_stu_bg` varchar(5) DEFAULT NULL,
  `r_stu_email` varchar(100) DEFAULT NULL,
  `r_stu_p_email` varchar(100) DEFAULT NULL,
  `r_stu_mtoung` varchar(50) DEFAULT NULL,
  `r_stu_nati` varchar(50) DEFAULT NULL,
  `r_stu_jb` varchar(100) DEFAULT NULL,
  `r_stu_vot` enum('Yes','No') DEFAULT NULL,
  `r_stu_vot_no` varchar(20) DEFAULT NULL,
  `r_stu_org` varchar(100) DEFAULT NULL,
  `r_stu_sport` varchar(100) DEFAULT NULL,
  `r_stu_intr_ncc` enum('Yes','No') DEFAULT NULL,
  `subjects_json` text DEFAULT NULL,
  `r_stu_bkn` varchar(100) DEFAULT NULL,
  `r_stu_ifsc` varchar(20) DEFAULT NULL,
  `r_stu_bkacc` varchar(30) DEFAULT NULL,
  `r_stu_adhr_lnk` enum('Yes','No') DEFAULT NULL,
  `r_stu_exam` varchar(100) DEFAULT NULL,
  `r_uni` varchar(100) DEFAULT NULL,
  `r_seat` varchar(50) DEFAULT NULL,
  `r_mrk_obt` int(11) DEFAULT NULL,
  `r_perc` decimal(5,2) DEFAULT NULL,
  `r_sch` varchar(100) DEFAULT NULL,
  `r_sub1` varchar(50) DEFAULT NULL,
  `r_mrk1` int(11) DEFAULT NULL,
  `r_sub2` varchar(50) DEFAULT NULL,
  `r_mrk2` int(11) DEFAULT NULL,
  `r_sub3` varchar(50) DEFAULT NULL,
  `r_mrk3` int(11) DEFAULT NULL,
  `r_sub4` varchar(50) DEFAULT NULL,
  `r_mrk4` int(11) DEFAULT NULL,
  `r_sub5` varchar(50) DEFAULT NULL,
  `r_mrk5` int(11) DEFAULT NULL,
  `r_sub6` varchar(50) DEFAULT NULL,
  `r_mrk6` int(11) DEFAULT NULL,
  `r_sub7` varchar(50) DEFAULT NULL,
  `r_mrk7` int(11) DEFAULT NULL,
  `r_sub8` varchar(50) DEFAULT NULL,
  `r_mrk8` int(11) DEFAULT NULL,
  `doc1` varchar(255) DEFAULT NULL,
  `doc2` varchar(255) DEFAULT NULL,
  `doc3` varchar(255) DEFAULT NULL,
  `doc4` varchar(255) DEFAULT NULL,
  `doc5` varchar(255) DEFAULT NULL,
  `doc6` varchar(255) DEFAULT NULL,
  `doc7` varchar(255) DEFAULT NULL,
  `doc8` varchar(255) DEFAULT NULL,
  `r_stu_mother_ph_no` varchar(15) DEFAULT NULL,
  `s_stu_mother_Occ` varchar(100) DEFAULT NULL,
  `s_stu_mother_Ophno` varchar(15) DEFAULT NULL,
  `s_stu_mother_Oadd` text DEFAULT NULL,
  `r_stu_father_ph_no` varchar(15) DEFAULT NULL,
  `r_stu_father_Occ` varchar(100) DEFAULT NULL,
  `r_stu_father_Ophno` varchar(15) DEFAULT NULL,
  `r_stu_father_Oadd` text DEFAULT NULL,
  `r_stu_p_add` text DEFAULT NULL,
  `r_stu_inc` text DEFAULT NULL,
  `r_stu_rel` varchar(50) DEFAULT NULL,
  `type` enum('paying','non-paying') DEFAULT 'paying',
  `status` enum('Registered','Verified','Admitted') DEFAULT 'Admitted',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `comp_count` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `opt_count` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `tot_count` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `acad_yr` varchar(9) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admts`
--

INSERT INTO `admts` (`r_id`, `prn_no`, `r_stu_admi_cls`, `r_stu_tit`, `r_stu_mother`, `r_stu_gen`, `r_stu_id`, `r_stu_sig`, `r_stu_name`, `r_stu_father`, `r_stu_sur`, `r_p_add`, `r_stu_vil`, `r_sub_dist`, `r_dist`, `r_r_add`, `r_stu_ph`, `r_stu_G_ph`, `r_stu_B`, `r_stu_B_sub_dist`, `r_stu_B_dist`, `r_stu_B_city`, `r_stu_B_sta`, `r_stu_B_date`, `r_stu_B_dateW`, `r_stu_age`, `r_stu_disb`, `r_stu_mari`, `r_stu_reli`, `r_stu_cast`, `r_stu_castcat`, `r_stu_aadr`, `r_stu_pan`, `r_stu_bg`, `r_stu_email`, `r_stu_p_email`, `r_stu_mtoung`, `r_stu_nati`, `r_stu_jb`, `r_stu_vot`, `r_stu_vot_no`, `r_stu_org`, `r_stu_sport`, `r_stu_intr_ncc`, `subjects_json`, `r_stu_bkn`, `r_stu_ifsc`, `r_stu_bkacc`, `r_stu_adhr_lnk`, `r_stu_exam`, `r_uni`, `r_seat`, `r_mrk_obt`, `r_perc`, `r_sch`, `r_sub1`, `r_mrk1`, `r_sub2`, `r_mrk2`, `r_sub3`, `r_mrk3`, `r_sub4`, `r_mrk4`, `r_sub5`, `r_mrk5`, `r_sub6`, `r_mrk6`, `r_sub7`, `r_mrk7`, `r_sub8`, `r_mrk8`, `doc1`, `doc2`, `doc3`, `doc4`, `doc5`, `doc6`, `doc7`, `doc8`, `r_stu_mother_ph_no`, `s_stu_mother_Occ`, `s_stu_mother_Ophno`, `s_stu_mother_Oadd`, `r_stu_father_ph_no`, `r_stu_father_Occ`, `r_stu_father_Ophno`, `r_stu_father_Oadd`, `r_stu_p_add`, `r_stu_inc`, `r_stu_rel`, `type`, `status`, `created_at`, `comp_count`, `opt_count`, `tot_count`, `acad_yr`) VALUES
(3593, 2025100002, 'BACHELOR OF COMPUTER APPLICATIONS - BCA Part 2', 'Miss.', 'Vaishnavi', 'Female', NULL, NULL, 'Kalyani', 'Mahendra ', 'Kangralkar', 'Plot 18, Ayodhya Colony kalamba ring road', 'Kolhapur', 'Karveer', 'Karveer', 'Plot 18, Ayodhya Colony kalamba ring road', '09307 85685', '79723 78977', 'Kolhapur', 'Karveer', 'Karveer', 'Kolhapur', 'Maharashtra', '2006-10-16', 'Sixteenth October Two Thousand Six', 18, 'No', 'Single', 'Hindu', 'Maratha', 'OPEN', '4738 0904 4051', 'OSHPK9353K', 'A+', 'shrinivaskangralkar8055@gmail.com', 'shrinivaskangralkar8055@gmail.com', 'Marathi', 'INDIA', 'Student', 'No', '5545450000', 'Yes', 'Yes', 'Yes', '{\"compulsory\":[\"17\",\"18\",\"19\",\"20\",\"21\",\"22\",\"62\"],\"optional\":[\"23\"]}', 'Bank of Maharashtra', 'SBIN0003456', '65131351312321', 'Yes', 'SSC', 'SSC', '125689', 500, 87.00, 'Mahaveer English School', 'English', 87, 'Maths-1', 87, 'Maths-2', 87, 'Marathi ', 87, 'Hindi', 87, 'Science', 88, '', 0, '', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '9307856854', 'House wife', '', '', '9307856854', 'Business', '', '', 'Plot 18, Ayodhya Colony kalamba ring road', '> 50,000 Rs', 'Daughter', 'paying', 'Admitted', '2025-09-21 17:59:45', 7, 1, 8, '2025-2026'),
(5089, 2025100006, 'BACHELOR OF COMPUTER APPLICATIONS - BCA Part 1', 'Miss.', 'Vaishnavi', 'Female', NULL, NULL, 'Kalyani', 'Mahendra ', 'Kangralkar', 'Plot 18, Ayodhya Colony kalamba ring road', 'Kolhapur', 'Karveer', 'Karveer', 'Plot 18, Ayodhya Colony kalamba ring road', '09307 85685', '79723 78977', 'Kolhapur', 'Karveer', 'Karveer', 'Kolhapur', 'Maharashtra', '2006-10-16', 'Sixteenth October Two Thousand Six', 18, 'No', 'Single', 'Hindu', 'Maratha', 'OPEN', '4738 0904 4051', 'OSHPK9353K', 'A+', 'shrinivaskangralkar8055@gmail.com', 'shrinivaskangralkar8055@gmail.com', 'Marathi', 'INDIA', 'Student', 'No', '5545450000', 'Yes', 'Yes', 'Yes', '{\"compulsory\":[\"6\",\"7\"],\"optional\":[\"8\"]}', 'Bank of Maharashtra', 'SBIN0003456', '65131351312321', 'Yes', 'SSC', 'SSC', '125689', 500, 87.00, 'Mahaveer English School', 'English', 87, 'Maths-1', 87, 'Maths-2', 87, 'Marathi ', 87, 'Hindi', 87, 'Science', 88, '', 0, '', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '9307856854', 'House wife', '', '', '9307856854', 'Business', '', '', 'Plot 18, Ayodhya Colony kalamba ring road', '> 50,000 Rs', 'Daughter', 'paying', 'Admitted', '2025-09-22 06:49:58', 2, 1, 3, '2025-2026'),
(5608, 2025100005, 'BACHELOR OF COMPUTER APPLICATIONS - BCA Part 1', 'Miss.', 'Vaishnavi', 'Female', NULL, NULL, 'Kalyani', 'Mahendra ', 'Kangralkar', 'Plot 18, Ayodhya Colony kalamba ring road', 'Kolhapur', 'Karveer', 'Karveer', 'Plot 18, Ayodhya Colony kalamba ring road', '09307 85685', '79723 78977', 'Kolhapur', 'Karveer', 'Karveer', 'Kolhapur', 'Maharashtra', '2006-10-16', 'Sixteenth October Two Thousand Six', 18, 'No', 'Single', 'Hindu', 'Maratha', 'OPEN', '4738 0904 4051', 'OSHPK9353K', 'A+', 'shrinivaskangralkar8055@gmail.com', 'shrinivaskangralkar8055@gmail.com', 'Marathi', 'INDIA', 'Student', 'No', '5545450000', 'Yes', 'Yes', 'Yes', '{\"compulsory\":[\"1\",\"2\"],\"optional\":[\"8\"]}', 'Bank of Maharashtra', 'SBIN0003456', '65131351312321', 'Yes', 'SSC', 'SSC', '125689', 500, 87.00, 'Mahaveer English School', 'English', 87, 'Maths-1', 87, 'Maths-2', 87, 'Marathi ', 87, 'Hindi', 87, 'Science', 88, '', 0, '', 0, 'uploads/students/5608/doc1_1758523633_2969.pdf', 'uploads/students/5608/doc2_1758523633_6820.pdf', 'uploads/students/5608/doc3_1758523633_5742.pdf', 'uploads/students/5608/doc4_1758523633_3170.pdf', NULL, NULL, NULL, NULL, '9307856854', 'House wife', '', '', '9307856854', 'Business', '', '', 'Plot 18, Ayodhya Colony kalamba ring road', '> 50,000 Rs', 'Daughter', 'paying', 'Admitted', '2025-09-22 06:47:43', 2, 1, 3, '2025-2026'),
(6386, 2025100007, 'BACHELOR OF COMPUTER APPLICATIONS - BCA Part 2', 'Miss.', 'Vaishnavi', 'Female', NULL, NULL, 'Kalyani', 'Mahendra ', 'Kangralkar', 'Plot 18, Ayodhya Colony kalamba ring road', 'Kolhapur', 'Karveer', 'Karveer', 'Plot 18, Ayodhya Colony kalamba ring road', '09307 85685', '79723 78977', 'Kolhapur', 'Karveer', 'Karveer', 'Kolhapur', 'Maharashtra', '2006-10-16', 'Sixteenth October Two Thousand Six', 18, 'No', 'Single', 'Hindu', 'Maratha', 'OPEN', '4738 0904 4051', 'OSHPK9353K', 'A+', 'shrinivaskangralkar8055@gmail.com', 'shrinivaskangralkar8055@gmail.com', 'Marathi', 'INDIA', 'Student', 'No', '5545450000', 'Yes', 'Yes', 'Yes', '{\"compulsory\":[\"17\",\"18\",\"19\",\"20\",\"21\",\"22\",\"62\"],\"optional\":[\"23\"]}', 'Bank of Maharashtra', 'SBIN0003456', '65131351312321', 'Yes', 'SSC', 'SSC', '125689', 500, 87.00, 'Mahaveer English School', 'English', 87, 'Maths-1', 87, 'Maths-2', 87, 'Marathi ', 87, 'Hindi', 87, 'Science', 88, '', 0, '', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '9307856854', 'House wife', '', '', '9307856854', 'Business', '', '', 'Plot 18, Ayodhya Colony kalamba ring road', '> 50,000 Rs', 'Daughter', 'paying', 'Admitted', '2025-09-22 06:53:59', 7, 1, 8, '2025-2026'),
(6511, 2025100004, 'BACHELOR OF COMPUTER APPLICATIONS - BCA Part 2', 'Miss.', 'Vaishnavi', 'Female', NULL, NULL, 'Kalyani', 'Mahendra ', 'Kangralkar', 'Plot 18, Ayodhya Colony kalamba ring road', 'Kolhapur', 'Karveer', 'Karveer', 'Plot 18, Ayodhya Colony kalamba ring road', '09307 85685', '79723 78977', 'Kolhapur', 'Karveer', 'Karveer', 'Kolhapur', 'Maharashtra', '2006-10-16', 'Sixteenth October Two Thousand Six', 18, 'No', 'Single', 'Hindu', 'Maratha', 'OPEN', '4738 0904 4051', 'OSHPK9353K', 'A+', 'shrinivaskangralkar8055@gmail.com', 'shrinivaskangralkar8055@gmail.com', 'Marathi', 'INDIA', 'Student', 'No', '5545450000', 'Yes', 'Yes', 'Yes', '{\"compulsory\":[\"17\",\"18\",\"19\",\"20\",\"21\",\"22\",\"62\"],\"optional\":[\"23\"]}', 'Bank of Maharashtra', 'SBIN0003456', '65131351312321', 'Yes', 'SSC', 'SSC', '125689', 500, 87.00, 'Mahaveer English School', 'English', 87, 'Maths-1', 87, 'Maths-2', 87, 'Marathi ', 87, 'Hindi', 87, 'Science', 88, '', 0, '', 0, 'uploads/students/6511/doc1_1758478851_9712.pdf', 'uploads/students/6511/doc2_1758478851_1913.pdf', 'uploads/students/6511/doc3_1758478851_7656.pdf', 'uploads/students/6511/doc4_1758478851_9304.pdf', NULL, NULL, NULL, NULL, '9307856854', 'House wife', '', '', '9307856854', 'Business', '', '', 'Plot 18, Ayodhya Colony kalamba ring road', '> 50,000 Rs', 'Daughter', 'paying', 'Admitted', '2025-09-21 18:21:19', 7, 1, 8, '2025-2026'),
(9019, 2025100001, 'BACHELOR OF COMPUTER APPLICATIONS - BCA Part 1', 'Miss.', 'Vaishnavi', 'Female', NULL, NULL, 'Kalyani', 'Mahendra ', 'Kangralkar', 'Plot 18, Ayodhya Colony kalamba ring road', 'Kolhapur', 'Karveer', 'Karveer', 'Plot 18, Ayodhya Colony kalamba ring road', '09307 85685', '79723 78977', 'Kolhapur', 'Karveer', 'Karveer', 'Kolhapur', 'Maharashtra', '2006-10-16', 'Sixteenth October Two Thousand Six', 18, 'No', 'Single', 'Hindu', 'Maratha', 'OPEN', '4738 0904 4051', 'OSHPK9353K', 'A+', 'shrinivaskangralkar8055@gmail.com', 'shrinivaskangralkar8055@gmail.com', 'Marathi', 'INDIA', 'Student', 'No', '5545450000', 'Yes', 'Yes', 'Yes', '{\"compulsory\":[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\",\"7\"],\"optional\":[\"8\"]}', 'Bank of Maharashtra', 'SBIN0003456', '65131351312321', 'Yes', 'SSC', 'SSC', '125689', 500, 87.00, 'Mahaveer English School', 'English', 87, 'Maths-1', 87, 'Maths-2', 87, 'Marathi ', 87, 'Hindi', 87, 'Science', 88, '', 0, '', 0, 'uploads/students/9019/doc1_1758477473_9087.pdf', 'uploads/students/9019/doc2_1758477473_5249.pdf', 'uploads/students/9019/doc3_1758477473_5369.pdf', 'uploads/students/9019/doc4_1758477473_8969.pdf', NULL, NULL, NULL, NULL, '9307856854', 'House wife', '', '', '9307856854', 'Business', '', '', 'Plot 18, Ayodhya Colony kalamba ring road', '> 50,000 Rs', 'Daughter', 'paying', 'Admitted', '2025-09-21 17:58:31', 7, 1, 8, '2025-2026'),
(9352, 2025100008, 'BACHELOR OF COMPUTER APPLICATIONS - BCA Part 3', 'Miss.', 'Vaishnavi', 'Female', NULL, NULL, 'Kalyani', 'Mahendra ', 'Kangralkar', 'Plot 18, Ayodhya Colony kalamba ring road', 'Kolhapur', 'Karveer', 'Karveer', 'Plot 18, Ayodhya Colony kalamba ring road', '09307 85685', '79723 78977', 'Kolhapur', 'Karveer', 'Karveer', 'Kolhapur', 'Maharashtra', '2006-10-16', 'Sixteenth October Two Thousand Six', 18, 'No', 'Single', 'Hindu', 'Maratha', 'OPEN', '4738 0904 4051', 'OSHPK9353K', 'A+', 'shrinivaskangralkar8055@gmail.com', 'shrinivaskangralkar8055@gmail.com', 'Marathi', 'INDIA', 'Student', 'No', '5545450000', 'Yes', 'Yes', 'Yes', '{\"compulsory\":[\"31\",\"32\",\"33\",\"34\",\"35\",\"36\"],\"optional\":[\"37\"]}', 'Bank of Maharashtra', 'SBIN0003456', '65131351312321', 'Yes', 'SSC', 'SSC', '125689', 500, 87.00, 'Mahaveer English School', 'English', 87, 'Maths-1', 87, 'Maths-2', 87, 'Marathi ', 87, 'Hindi', 87, 'Science', 88, '', 0, '', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '9307856854', 'House wife', '', '', '9307856854', 'Business', '', '', 'Plot 18, Ayodhya Colony kalamba ring road', '> 50,000 Rs', 'Daughter', 'paying', 'Admitted', '2025-09-22 06:59:07', 6, 1, 7, '2025-2026'),
(9464, 2025100003, 'BACHELOR OF COMPUTER APPLICATIONS - BCA Part 2', 'Miss.', 'Vaishnavi', 'Female', NULL, NULL, 'Kalyani', 'Mahendra ', 'Kangralkar', 'Plot 18, Ayodhya Colony kalamba ring road', 'Kolhapur', 'Karveer', 'Karveer', 'Plot 18, Ayodhya Colony kalamba ring road', '09307 85685', '79723 78977', 'Kolhapur', 'Karveer', 'Karveer', 'Kolhapur', 'Maharashtra', '2006-10-16', 'Sixteenth October Two Thousand Six', 18, 'No', 'Single', 'Hindu', 'Maratha', 'OPEN', '4738 0904 4051', 'OSHPK9353K', 'A+', 'shrinivaskangralkar8055@gmail.com', 'shrinivaskangralkar8055@gmail.com', 'Marathi', 'INDIA', 'Student', 'No', '5545450000', 'Yes', 'Yes', 'Yes', '{\"compulsory\":[\"17\",\"18\",\"19\",\"20\",\"21\",\"22\",\"62\"],\"optional\":[\"23\"]}', 'Bank of Maharashtra', 'SBIN0003456', '65131351312321', 'Yes', 'SSC', 'SSC', '125689', 500, 87.00, 'Mahaveer English School', 'English', 87, 'Maths-1', 87, 'Maths-2', 87, 'Marathi ', 87, 'Hindi', 87, 'Science', 88, '', 0, '', 0, 'uploads/students/9464/doc1_1758477899_1899.pdf', 'uploads/students/9464/doc2_1758477899_7252.pdf', 'uploads/students/9464/doc3_1758477899_8010.pdf', 'uploads/students/9464/doc4_1758477899_9259.pdf', NULL, NULL, NULL, NULL, '9307856854', 'House wife', '', '', '9307856854', 'Business', '', '', 'Plot 18, Ayodhya Colony kalamba ring road', '> 50,000 Rs', 'Daughter', 'paying', 'Admitted', '2025-09-21 18:05:25', 7, 1, 8, '2025-2026');

-- --------------------------------------------------------

--
-- Table structure for table `caste_m`
--

CREATE TABLE `caste_m` (
  `caste_id` int(11) NOT NULL,
  `c_sh_caste` varchar(50) NOT NULL,
  `c_ful_caste` varchar(150) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `caste_m`
--

INSERT INTO `caste_m` (`caste_id`, `c_sh_caste`, `c_ful_caste`, `created_at`) VALUES
(8, 'MARATHA', 'Maratha', '2025-08-16 20:41:03'),
(9, 'DHANGAR', 'Dhangar', '2025-08-16 20:41:35'),
(10, 'KUMBHAR', 'Kumbhar', '2025-08-16 20:41:44'),
(11, 'KOLI', 'Koli', '2025-08-16 20:41:53'),
(12, 'SHIYA', 'Shiya', '2025-08-16 20:42:21'),
(13, 'SUNNI', 'Sunni', '2025-08-16 20:42:31'),
(14, 'MULLA', 'Mulla', '2025-08-16 20:42:55'),
(15, 'KHATIK', 'Khatik', '2025-08-16 20:43:06');

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `cls_id` int(11) NOT NULL,
  `stream` varchar(50) NOT NULL,
  `cls_shr_nm` varchar(50) NOT NULL,
  `cls_ful_nm` varchar(100) NOT NULL,
  `tot_div` int(11) NOT NULL,
  `tot_cap_cls` int(11) NOT NULL,
  `duration_years` int(11) DEFAULT 1,
  `total_terms` int(11) DEFAULT NULL,
  `pattern` enum('yearly','semester') NOT NULL,
  `fpattern` varchar(50) DEFAULT NULL,
  `cls_code` char(3) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`cls_id`, `stream`, `cls_shr_nm`, `cls_ful_nm`, `tot_div`, `tot_cap_cls`, `duration_years`, `total_terms`, `pattern`, `fpattern`, `cls_code`) VALUES
(32, 'SCIENCE', 'SCI : PCMB', 'SCIENCE : PHYSICS, CHEMISTRY, MATHS, BIOLOGY', 3, 210, 2, 2, 'yearly', 'yearly', '150'),
(35, 'BCA', 'BCA', 'BACHELOR OF COMPUTER APPLICATIONS', 2, 160, 3, 6, 'semester', 'yearly', '100'),
(36, 'BA', 'BA', 'BACHLOR OF ARTS', 2, 160, 3, 6, 'semester', 'yearly', '200'),
(37, 'B.COM', 'B.Com', 'BACHLOR OF COMMERCE', 2, 160, 3, 6, 'semester', 'yearly', '250'),
(38, 'B.VOC', 'B.Voc', 'BACHLOR OF VOCATION', 2, 160, 3, 6, 'semester', 'yearly', '300'),
(39, 'DIPLOMA', 'Diploma CSE', 'DIPLOMA IN COMPUTER SCIENCE AND ENGINEERING', 2, 160, 3, 6, 'semester', 'yearly', '350'),
(40, 'SCIENCE', 'SCI : PCM', 'SCIENCE : PHYSICS, CHEMISTRY, MATHS', 3, 210, 2, 2, 'yearly', 'yearly', '400'),
(41, 'SCIENCE', 'SCI : PCB', 'SCIENCE : PHYSICS, CHEMISTRY, BIOLOGY', 3, 210, 2, 2, 'yearly', 'yearly', '450'),
(42, 'Architecture', 'B.Arch', 'BACHLOR OF ARCHITECTURE', 2, 160, 5, 10, 'semester', 'yearly', '500');

-- --------------------------------------------------------

--
-- Table structure for table `feecls`
--

CREATE TABLE `feecls` (
  `term_id` int(11) NOT NULL,
  `cls_id` int(11) NOT NULL,
  `cls_ful_nm` varchar(100) NOT NULL,
  `term_label` varchar(50) NOT NULL,
  `term_title` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feecls`
--

INSERT INTO `feecls` (`term_id`, `cls_id`, `cls_ful_nm`, `term_label`, `term_title`, `created_at`) VALUES
(36, 36, 'BACHLOR OF ARTS', 'SEM I', 'BA Part 1', '2025-09-04 07:38:29'),
(37, 36, 'BACHLOR OF ARTS', 'SEM III', 'BA Part 2', '2025-09-04 07:38:29'),
(38, 36, 'BACHLOR OF ARTS', 'SEM V', 'BA Part 3', '2025-09-04 07:38:29'),
(39, 37, 'BACHLOR OF COMMERCE', 'SEM I', 'B.com Part 1', '2025-09-04 08:40:45'),
(40, 37, 'BACHLOR OF COMMERCE', 'SEM III', 'B.com Part 2', '2025-09-04 08:40:45'),
(41, 37, 'BACHLOR OF COMMERCE', 'SEM V', 'B.com Part 3', '2025-09-04 08:40:45'),
(42, 38, 'BACHLOR OF VOCATION', 'SEM I', 'B.Voc Part 1', '2025-09-04 08:42:57'),
(43, 38, 'BACHLOR OF VOCATION', 'SEM III', 'B.Voc Part 2', '2025-09-04 08:42:57'),
(44, 38, 'BACHLOR OF VOCATION', 'SEM V', 'B.Voc Part 3', '2025-09-04 08:42:57'),
(45, 39, 'DIPLOMA IN COMPUTER SCIENCE AND ENGINEERING', 'SEM I', 'FY CSE', '2025-09-04 08:49:37'),
(46, 39, 'DIPLOMA IN COMPUTER SCIENCE AND ENGINEERING', 'SEM III', 'SY CSE', '2025-09-04 08:49:37'),
(47, 39, 'DIPLOMA IN COMPUTER SCIENCE AND ENGINEERING', 'SEM V', 'TY CSE', '2025-09-04 08:49:37'),
(50, 32, 'SCIENCE : PHYSICS, CHEMISTRY, MATHS, BIOLOGY', 'Year 1', '11th Science : PCMB', '2025-09-04 09:00:23'),
(51, 32, 'SCIENCE : PHYSICS, CHEMISTRY, MATHS, BIOLOGY', 'Year 2', '12th Science : PCMB', '2025-09-04 09:00:23'),
(54, 40, 'SCIENCE : PHYSICS, CHEMISTRY, MATHS', 'Year 1', '11th Science : PCM', '2025-09-04 09:02:13'),
(55, 40, 'SCIENCE : PHYSICS, CHEMISTRY, MATHS', 'Year 2', '12th Science : PCM', '2025-09-04 09:02:13'),
(56, 41, 'SCIENCE : PHYSICS, CHEMISTRY, BIOLOGY', 'Year 1', '11th Science : PCB', '2025-09-04 09:03:31'),
(57, 41, 'SCIENCE : PHYSICS, CHEMISTRY, BIOLOGY', 'Year 2', '12th Science : PCB', '2025-09-04 09:03:31'),
(58, 42, 'BACHLOR OF ARCHITECTURE', 'SEM I', 'B.Arch Year 1', '2025-09-04 09:06:36'),
(59, 42, 'BACHLOR OF ARCHITECTURE', 'SEM III', 'B.Arch Year 2', '2025-09-04 09:06:36'),
(60, 42, 'BACHLOR OF ARCHITECTURE', 'SEM V', 'B.Arch Year 3', '2025-09-04 09:06:36'),
(61, 42, 'BACHLOR OF ARCHITECTURE', 'SEM VII', 'B.Arch Year 4', '2025-09-04 09:06:36'),
(62, 42, 'BACHLOR OF ARCHITECTURE', 'SEM IX', 'B.Arch Year 5', '2025-09-04 09:06:36'),
(63, 35, 'BACHELOR OF COMPUTER APPLICATIONS', 'SEM I', 'BCA Part 1', '2025-09-12 16:17:29'),
(64, 35, 'BACHELOR OF COMPUTER APPLICATIONS', 'SEM III', 'BCA Part 2', '2025-09-12 16:17:29'),
(65, 35, 'BACHELOR OF COMPUTER APPLICATIONS', 'SEM V', 'BCA Part 3', '2025-09-12 16:17:29');

-- --------------------------------------------------------

--
-- Table structure for table `feestru`
--

CREATE TABLE `feestru` (
  `fee_id` int(11) NOT NULL,
  `term_id` int(11) NOT NULL,
  `type` enum('paying','non_paying') NOT NULL,
  `sh_nm` varchar(50) NOT NULL,
  `fl_nm` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feestru`
--

INSERT INTO `feestru` (`fee_id`, `term_id`, `type`, `sh_nm`, `fl_nm`, `amount`) VALUES
(112, 63, 'paying', 'EF', 'Entry Fee', 500.00),
(113, 63, 'paying', 'TF', 'Tution Fee', 10000.00),
(114, 63, 'paying', 'GYM F', 'GYM Fee', 150.00),
(115, 63, 'paying', 'ID', 'Id', 50.00),
(116, 63, 'paying', 'GF', 'General Fee', 100.00),
(117, 63, 'non_paying', 'EF', 'Entry Fee', 500.00),
(118, 63, 'non_paying', 'TF', 'Tution Fee', 0.00),
(119, 63, 'non_paying', 'GYM F', 'GYM Fee', 150.00),
(120, 63, 'non_paying', 'ID', 'Id', 50.00),
(121, 63, 'non_paying', 'GF', 'General Fee', 100.00),
(122, 63, 'paying', '-', 'Total', 10800.00),
(123, 63, 'non_paying', '-', 'Total', 800.00),
(124, 64, 'paying', 'EF', 'Entry Fee', 500.00),
(125, 64, 'paying', 'TF', 'Tution Fee', 15000.00),
(126, 64, 'paying', 'GYM F', 'GYM Fee', 150.00),
(127, 64, 'paying', 'ID', 'Id', 50.00),
(128, 64, 'paying', 'GF', 'General Fee', 100.00),
(129, 64, 'non_paying', 'EF', 'Entry Fee', 500.00),
(130, 64, 'non_paying', 'TF', 'Tution Fee', 0.00),
(131, 64, 'non_paying', 'GYM F', 'GYM Fee', 150.00),
(132, 64, 'non_paying', 'ID', 'Id', 50.00),
(133, 64, 'non_paying', 'GF', 'General Fee', 100.00),
(134, 64, 'paying', '-', 'Total', 15800.00),
(135, 64, 'non_paying', '-', 'Total', 800.00),
(136, 65, 'paying', 'EF', 'Entry Fee', 500.00),
(137, 65, 'paying', 'TF', 'Tution Fee', 20000.00),
(138, 65, 'paying', 'GYM F', 'GYM Fee', 150.00),
(139, 65, 'paying', 'ID', 'Id', 50.00),
(140, 65, 'paying', 'GF', 'General Fee', 100.00),
(141, 65, 'non_paying', 'EF', 'Entry Fee', 500.00),
(142, 65, 'non_paying', 'TF', 'Tution Fee', 0.00),
(143, 65, 'non_paying', 'GYM F', 'GYM Fee', 150.00),
(144, 65, 'non_paying', 'ID', 'Id', 50.00),
(145, 65, 'non_paying', 'GF', 'General Fee', 100.00),
(146, 65, 'paying', '-', 'Total', 20800.00),
(147, 65, 'non_paying', '-', 'Total', 800.00),
(148, 50, 'paying', 'EF', 'Entry Fee', 500.00),
(149, 50, 'paying', 'TF', 'Tution Fee', 20000.00),
(150, 50, 'paying', 'GYM F', 'GYM Fee', 150.00),
(151, 50, 'paying', 'ID', 'Id', 100.00),
(152, 50, 'paying', 'GF', 'General Fee', 150.00),
(153, 50, 'non_paying', 'EF', 'Entry Fee', 500.00),
(154, 50, 'non_paying', 'TF', 'Tution Fee', 0.00),
(155, 50, 'non_paying', 'GYM F', 'GYM Fee', 150.00),
(156, 50, 'non_paying', 'ID', 'Id', 100.00),
(157, 50, 'non_paying', 'GF', 'General Fee', 150.00),
(158, 50, 'paying', '-', 'Total', 20900.00),
(159, 50, 'non_paying', '-', 'Total', 900.00),
(160, 51, 'paying', 'EF', 'Entry Fee', 500.00),
(161, 51, 'paying', 'TF', 'Tution Fee', 25000.00),
(162, 51, 'paying', 'GYM F', 'GYM Fee', 150.00),
(163, 51, 'paying', 'ID', 'Id', 100.00),
(164, 51, 'paying', 'GF', 'General Fee', 150.00),
(165, 51, 'non_paying', 'EF', 'Entry Fee', 500.00),
(166, 51, 'non_paying', 'TF', 'Tution Fee', 0.00),
(167, 51, 'non_paying', 'GYM F', 'GYM Fee', 150.00),
(168, 51, 'non_paying', 'ID', 'Id', 100.00),
(169, 51, 'non_paying', 'GF', 'General Fee', 150.00),
(170, 51, 'paying', '-', 'Total', 25900.00),
(171, 51, 'non_paying', '-', 'Total', 900.00);

-- --------------------------------------------------------

--
-- Table structure for table `religion_m`
--

CREATE TABLE `religion_m` (
  `rel_id` int(11) NOT NULL,
  `sh_nm` varchar(50) NOT NULL,
  `fl_nm` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `religion_m`
--

INSERT INTO `religion_m` (`rel_id`, `sh_nm`, `fl_nm`, `created_at`) VALUES
(4, 'HINDU', 'Hindu', '2025-08-16 20:39:00'),
(5, 'JAIN', 'Jain', '2025-08-16 20:39:10'),
(6, 'CHRIS', 'Christen', '2025-08-16 20:39:28'),
(7, 'ISLAM', 'Islam', '2025-08-16 20:39:49'),
(8, 'MUSLIM', 'Muslim', '2025-08-16 20:40:00'),
(9, 'SHIKH', 'Shikh', '2025-08-16 20:40:23');

-- --------------------------------------------------------

--
-- Table structure for table `student_registration`
--

CREATE TABLE `student_registration` (
  `r_id` int(4) NOT NULL,
  `r_stu_admi_cls` varchar(100) DEFAULT NULL,
  `r_stu_tit` varchar(10) DEFAULT NULL,
  `r_stu_mother` varchar(100) DEFAULT NULL,
  `r_stu_gen` enum('Male','Female','Other') DEFAULT NULL,
  `r_stu_id` varchar(50) DEFAULT NULL,
  `r_stu_sig` varchar(255) DEFAULT NULL,
  `r_stu_name` varchar(100) DEFAULT NULL,
  `r_stu_father` varchar(100) DEFAULT NULL,
  `r_stu_sur` varchar(100) DEFAULT NULL,
  `r_p_add` text DEFAULT NULL,
  `r_stu_vil` varchar(100) DEFAULT NULL,
  `r_sub_dist` varchar(100) DEFAULT NULL,
  `r_dist` varchar(100) DEFAULT NULL,
  `r_r_add` text DEFAULT NULL,
  `r_stu_ph` varchar(15) DEFAULT NULL,
  `r_stu_G_ph` varchar(15) DEFAULT NULL,
  `r_stu_B` varchar(100) DEFAULT NULL,
  `r_stu_B_sub_dist` varchar(100) DEFAULT NULL,
  `r_stu_B_dist` varchar(100) DEFAULT NULL,
  `r_stu_B_city` varchar(100) DEFAULT NULL,
  `r_stu_B_sta` varchar(100) DEFAULT NULL,
  `r_stu_B_date` date DEFAULT NULL,
  `r_stu_B_dateW` varchar(50) DEFAULT NULL,
  `r_stu_age` int(11) DEFAULT NULL,
  `r_stu_disb` varchar(100) DEFAULT NULL,
  `r_stu_mari` enum('Single','Married','Other') DEFAULT NULL,
  `r_stu_reli` varchar(50) DEFAULT NULL,
  `r_stu_cast` varchar(50) DEFAULT NULL,
  `r_stu_castcat` varchar(50) DEFAULT NULL,
  `r_stu_aadr` varchar(14) DEFAULT NULL,
  `r_stu_pan` char(10) DEFAULT NULL,
  `r_stu_bg` varchar(5) DEFAULT NULL,
  `r_stu_email` varchar(100) DEFAULT NULL,
  `r_stu_p_email` varchar(100) DEFAULT NULL,
  `r_stu_mtoung` varchar(50) DEFAULT NULL,
  `r_stu_nati` varchar(50) DEFAULT NULL,
  `r_stu_jb` varchar(100) DEFAULT NULL,
  `r_stu_vot` enum('Yes','No') DEFAULT NULL,
  `r_stu_vot_no` varchar(20) DEFAULT NULL,
  `r_stu_org` varchar(100) DEFAULT NULL,
  `r_stu_sport` varchar(100) DEFAULT NULL,
  `r_stu_intr_ncc` enum('Yes','No') DEFAULT NULL,
  `subjects_json` text DEFAULT NULL,
  `r_stu_bkn` varchar(100) DEFAULT NULL,
  `r_stu_ifsc` varchar(20) DEFAULT NULL,
  `r_stu_bkacc` varchar(30) DEFAULT NULL,
  `r_stu_adhr_lnk` enum('Yes','No') DEFAULT NULL,
  `r_stu_exam` varchar(100) DEFAULT NULL,
  `r_uni` varchar(100) DEFAULT NULL,
  `r_seat` varchar(50) DEFAULT NULL,
  `r_mrk_obt` int(11) DEFAULT NULL,
  `r_perc` decimal(5,2) DEFAULT NULL,
  `r_sch` varchar(100) DEFAULT NULL,
  `r_sub1` varchar(50) DEFAULT NULL,
  `r_mrk1` int(11) DEFAULT NULL,
  `r_sub2` varchar(50) DEFAULT NULL,
  `r_mrk2` int(11) DEFAULT NULL,
  `r_sub3` varchar(50) DEFAULT NULL,
  `r_mrk3` int(11) DEFAULT NULL,
  `r_sub4` varchar(50) DEFAULT NULL,
  `r_mrk4` int(11) DEFAULT NULL,
  `r_sub5` varchar(50) DEFAULT NULL,
  `r_mrk5` int(11) DEFAULT NULL,
  `r_sub6` varchar(50) DEFAULT NULL,
  `r_mrk6` int(11) DEFAULT NULL,
  `r_sub7` varchar(50) DEFAULT NULL,
  `r_mrk7` int(11) DEFAULT NULL,
  `r_sub8` varchar(50) DEFAULT NULL,
  `r_mrk8` int(11) DEFAULT NULL,
  `doc1` varchar(255) DEFAULT NULL,
  `doc2` varchar(255) DEFAULT NULL,
  `doc3` varchar(255) DEFAULT NULL,
  `doc4` varchar(255) DEFAULT NULL,
  `doc5` varchar(255) DEFAULT NULL,
  `doc6` varchar(255) DEFAULT NULL,
  `doc7` varchar(255) DEFAULT NULL,
  `doc8` varchar(255) DEFAULT NULL,
  `r_stu_mother_ph_no` varchar(15) DEFAULT NULL,
  `s_stu_mother_Occ` varchar(100) DEFAULT NULL,
  `s_stu_mother_Ophno` varchar(15) DEFAULT NULL,
  `s_stu_mother_Oadd` text DEFAULT NULL,
  `r_stu_father_ph_no` varchar(15) DEFAULT NULL,
  `r_stu_father_Occ` varchar(100) DEFAULT NULL,
  `r_stu_father_Ophno` varchar(15) DEFAULT NULL,
  `r_stu_father_Oadd` text DEFAULT NULL,
  `r_stu_p_add` text DEFAULT NULL,
  `r_stu_inc` text DEFAULT NULL,
  `r_stu_rel` varchar(50) DEFAULT NULL,
  `type` enum('paying','non_paying') NOT NULL,
  `status` enum('Registered','Admitted','Not Admitted') DEFAULT 'Registered',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `comp_count` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `opt_count` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `tot_count` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `acad_yr` varchar(9) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_registration`
--

INSERT INTO `student_registration` (`r_id`, `r_stu_admi_cls`, `r_stu_tit`, `r_stu_mother`, `r_stu_gen`, `r_stu_id`, `r_stu_sig`, `r_stu_name`, `r_stu_father`, `r_stu_sur`, `r_p_add`, `r_stu_vil`, `r_sub_dist`, `r_dist`, `r_r_add`, `r_stu_ph`, `r_stu_G_ph`, `r_stu_B`, `r_stu_B_sub_dist`, `r_stu_B_dist`, `r_stu_B_city`, `r_stu_B_sta`, `r_stu_B_date`, `r_stu_B_dateW`, `r_stu_age`, `r_stu_disb`, `r_stu_mari`, `r_stu_reli`, `r_stu_cast`, `r_stu_castcat`, `r_stu_aadr`, `r_stu_pan`, `r_stu_bg`, `r_stu_email`, `r_stu_p_email`, `r_stu_mtoung`, `r_stu_nati`, `r_stu_jb`, `r_stu_vot`, `r_stu_vot_no`, `r_stu_org`, `r_stu_sport`, `r_stu_intr_ncc`, `subjects_json`, `r_stu_bkn`, `r_stu_ifsc`, `r_stu_bkacc`, `r_stu_adhr_lnk`, `r_stu_exam`, `r_uni`, `r_seat`, `r_mrk_obt`, `r_perc`, `r_sch`, `r_sub1`, `r_mrk1`, `r_sub2`, `r_mrk2`, `r_sub3`, `r_mrk3`, `r_sub4`, `r_mrk4`, `r_sub5`, `r_mrk5`, `r_sub6`, `r_mrk6`, `r_sub7`, `r_mrk7`, `r_sub8`, `r_mrk8`, `doc1`, `doc2`, `doc3`, `doc4`, `doc5`, `doc6`, `doc7`, `doc8`, `r_stu_mother_ph_no`, `s_stu_mother_Occ`, `s_stu_mother_Ophno`, `s_stu_mother_Oadd`, `r_stu_father_ph_no`, `r_stu_father_Occ`, `r_stu_father_Ophno`, `r_stu_father_Oadd`, `r_stu_p_add`, `r_stu_inc`, `r_stu_rel`, `type`, `status`, `created_at`, `comp_count`, `opt_count`, `tot_count`, `acad_yr`) VALUES
(3593, 'BACHELOR OF COMPUTER APPLICATIONS - BCA Part 2', 'Miss.', 'Vaishnavi', 'Female', NULL, NULL, 'Kalyani', 'Mahendra ', 'Kangralkar', 'Plot 18, Ayodhya Colony kalamba ring road', 'Kolhapur', 'Karveer', 'Karveer', 'Plot 18, Ayodhya Colony kalamba ring road', '09307 85685', '79723 78977', 'Kolhapur', 'Karveer', 'Karveer', 'Kolhapur', 'Maharashtra', '2006-10-16', 'Sixteenth October Two Thousand Six', 18, 'No', 'Single', 'Hindu', 'Maratha', 'OPEN', '4738 0904 4051', 'OSHPK9353K', 'A+', 'shrinivaskangralkar8055@gmail.com', 'shrinivaskangralkar8055@gmail.com', 'Marathi', 'INDIA', 'Student', 'No', '5545450000', 'Yes', 'Yes', 'Yes', '{\"compulsory\":[\"17\",\"18\",\"19\",\"20\",\"21\",\"22\",\"62\"],\"optional\":[\"23\"]}', 'Bank of Maharashtra', 'SBIN0003456', '65131351312321', 'Yes', 'SSC', 'SSC', '125689', 500, 87.00, 'Mahaveer English School', 'English', 87, 'Maths-1', 87, 'Maths-2', 87, 'Marathi ', 87, 'Hindi', 87, 'Science', 88, '', 0, '', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '9307856854', 'House wife', '', '', '9307856854', 'Business', '', '', 'Plot 18, Ayodhya Colony kalamba ring road', '> 50,000 Rs', 'Daughter', 'paying', 'Admitted', '2025-09-21 17:59:25', 7, 1, 8, '2025-2026'),
(5089, 'BACHELOR OF COMPUTER APPLICATIONS - BCA Part 1', 'Miss.', 'Vaishnavi', 'Female', NULL, NULL, 'Kalyani', 'Mahendra ', 'Kangralkar', 'Plot 18, Ayodhya Colony kalamba ring road', 'Kolhapur', 'Karveer', 'Karveer', 'Plot 18, Ayodhya Colony kalamba ring road', '09307 85685', '79723 78977', 'Kolhapur', 'Karveer', 'Karveer', 'Kolhapur', 'Maharashtra', '2006-10-16', 'Sixteenth October Two Thousand Six', 18, 'No', 'Single', 'Hindu', 'Maratha', 'OPEN', '4738 0904 4051', 'OSHPK9353K', 'A+', 'shrinivaskangralkar8055@gmail.com', 'shrinivaskangralkar8055@gmail.com', 'Marathi', 'INDIA', 'Student', 'No', '5545450000', 'Yes', 'Yes', 'Yes', '{\"compulsory\":[\"6\",\"7\"],\"optional\":[\"8\"]}', 'Bank of Maharashtra', 'SBIN0003456', '65131351312321', 'Yes', 'SSC', 'SSC', '125689', 500, 87.00, 'Mahaveer English School', 'English', 87, 'Maths-1', 87, 'Maths-2', 87, 'Marathi ', 87, 'Hindi', 87, 'Science', 88, '', 0, '', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '9307856854', 'House wife', '', '', '9307856854', 'Business', '', '', 'Plot 18, Ayodhya Colony kalamba ring road', '> 50,000 Rs', 'Daughter', 'paying', 'Admitted', '2025-09-22 06:49:42', 2, 1, 3, '2025-2026'),
(5608, 'BACHELOR OF COMPUTER APPLICATIONS - BCA Part 1', 'Miss.', 'Vaishnavi', 'Female', NULL, NULL, 'Kalyani', 'Mahendra ', 'Kangralkar', 'Plot 18, Ayodhya Colony kalamba ring road', 'Kolhapur', 'Karveer', 'Karveer', 'Plot 18, Ayodhya Colony kalamba ring road', '09307 85685', '79723 78977', 'Kolhapur', 'Karveer', 'Karveer', 'Kolhapur', 'Maharashtra', '2006-10-16', 'Sixteenth October Two Thousand Six', 18, 'No', 'Single', 'Hindu', 'Maratha', 'OPEN', '4738 0904 4051', 'OSHPK9353K', 'A+', 'shrinivaskangralkar8055@gmail.com', 'shrinivaskangralkar8055@gmail.com', 'Marathi', 'INDIA', 'Student', 'No', '5545450000', 'Yes', 'Yes', 'Yes', '{\"compulsory\":[\"1\",\"2\"],\"optional\":[\"8\"]}', 'Bank of Maharashtra', 'SBIN0003456', '65131351312321', 'Yes', 'SSC', 'SSC', '125689', 500, 87.00, 'Mahaveer English School', 'English', 87, 'Maths-1', 87, 'Maths-2', 87, 'Marathi ', 87, 'Hindi', 87, 'Science', 88, '', 0, '', 0, 'uploads/students/5608/doc1_1758523633_2969.pdf', 'uploads/students/5608/doc2_1758523633_6820.pdf', 'uploads/students/5608/doc3_1758523633_5742.pdf', 'uploads/students/5608/doc4_1758523633_3170.pdf', NULL, NULL, NULL, NULL, '9307856854', 'House wife', '', '', '9307856854', 'Business', '', '', 'Plot 18, Ayodhya Colony kalamba ring road', '> 50,000 Rs', 'Daughter', 'paying', 'Admitted', '2025-09-22 06:47:13', 2, 1, 3, '2025-2026'),
(6386, 'BACHELOR OF COMPUTER APPLICATIONS - BCA Part 2', 'Miss.', 'Vaishnavi', 'Female', NULL, NULL, 'Kalyani', 'Mahendra ', 'Kangralkar', 'Plot 18, Ayodhya Colony kalamba ring road', 'Kolhapur', 'Karveer', 'Karveer', 'Plot 18, Ayodhya Colony kalamba ring road', '09307 85685', '79723 78977', 'Kolhapur', 'Karveer', 'Karveer', 'Kolhapur', 'Maharashtra', '2006-10-16', 'Sixteenth October Two Thousand Six', 18, 'No', 'Single', 'Hindu', 'Maratha', 'OPEN', '4738 0904 4051', 'OSHPK9353K', 'A+', 'shrinivaskangralkar8055@gmail.com', 'shrinivaskangralkar8055@gmail.com', 'Marathi', 'INDIA', 'Student', 'No', '5545450000', 'Yes', 'Yes', 'Yes', '{\"compulsory\":[\"17\",\"18\",\"19\",\"20\",\"21\",\"22\",\"62\"],\"optional\":[\"23\"]}', 'Bank of Maharashtra', 'SBIN0003456', '65131351312321', 'Yes', 'SSC', 'SSC', '125689', 500, 87.00, 'Mahaveer English School', 'English', 87, 'Maths-1', 87, 'Maths-2', 87, 'Marathi ', 87, 'Hindi', 87, 'Science', 88, '', 0, '', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '9307856854', 'House wife', '', '', '9307856854', 'Business', '', '', 'Plot 18, Ayodhya Colony kalamba ring road', '> 50,000 Rs', 'Daughter', 'paying', 'Admitted', '2025-09-22 06:53:25', 7, 1, 8, '2025-2026'),
(6511, 'BACHELOR OF COMPUTER APPLICATIONS - BCA Part 2', 'Miss.', 'Vaishnavi', 'Female', NULL, NULL, 'Kalyani', 'Mahendra ', 'Kangralkar', 'Plot 18, Ayodhya Colony kalamba ring road', 'Kolhapur', 'Karveer', 'Karveer', 'Plot 18, Ayodhya Colony kalamba ring road', '09307 85685', '79723 78977', 'Kolhapur', 'Karveer', 'Karveer', 'Kolhapur', 'Maharashtra', '2006-10-16', 'Sixteenth October Two Thousand Six', 18, 'No', 'Single', 'Hindu', 'Maratha', 'OPEN', '4738 0904 4051', 'OSHPK9353K', 'A+', 'shrinivaskangralkar8055@gmail.com', 'shrinivaskangralkar8055@gmail.com', 'Marathi', 'INDIA', 'Student', 'No', '5545450000', 'Yes', 'Yes', 'Yes', '{\"compulsory\":[\"17\",\"18\",\"19\",\"20\",\"21\",\"22\",\"62\"],\"optional\":[\"23\"]}', 'Bank of Maharashtra', 'SBIN0003456', '65131351312321', 'Yes', 'SSC', 'SSC', '125689', 500, 87.00, 'Mahaveer English School', 'English', 87, 'Maths-1', 87, 'Maths-2', 87, 'Marathi ', 87, 'Hindi', 87, 'Science', 88, '', 0, '', 0, 'uploads/students/6511/doc1_1758478851_9712.pdf', 'uploads/students/6511/doc2_1758478851_1913.pdf', 'uploads/students/6511/doc3_1758478851_7656.pdf', 'uploads/students/6511/doc4_1758478851_9304.pdf', NULL, NULL, NULL, NULL, '9307856854', 'House wife', '', '', '9307856854', 'Business', '', '', 'Plot 18, Ayodhya Colony kalamba ring road', '> 50,000 Rs', 'Daughter', 'paying', 'Admitted', '2025-09-21 18:20:51', 7, 1, 8, '2025-2026'),
(9019, 'BACHELOR OF COMPUTER APPLICATIONS - BCA Part 1', 'Miss.', 'Vaishnavi', 'Female', NULL, NULL, 'Kalyani', 'Mahendra ', 'Kangralkar', 'Plot 18, Ayodhya Colony kalamba ring road', 'Kolhapur', 'Karveer', 'Karveer', 'Plot 18, Ayodhya Colony kalamba ring road', '09307 85685', '79723 78977', 'Kolhapur', 'Karveer', 'Karveer', 'Kolhapur', 'Maharashtra', '2006-10-16', 'Sixteenth October Two Thousand Six', 18, 'No', 'Single', 'Hindu', 'Maratha', 'OPEN', '4738 0904 4051', 'OSHPK9353K', 'A+', 'shrinivaskangralkar8055@gmail.com', 'shrinivaskangralkar8055@gmail.com', 'Marathi', 'INDIA', 'Student', 'No', '5545450000', 'Yes', 'Yes', 'Yes', '{\"compulsory\":[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\",\"7\"],\"optional\":[\"8\"]}', 'Bank of Maharashtra', 'SBIN0003456', '65131351312321', 'Yes', 'SSC', 'SSC', '125689', 500, 87.00, 'Mahaveer English School', 'English', 87, 'Maths-1', 87, 'Maths-2', 87, 'Marathi ', 87, 'Hindi', 87, 'Science', 88, '', 0, '', 0, 'uploads/students/9019/doc1_1758477473_9087.pdf', 'uploads/students/9019/doc2_1758477473_5249.pdf', 'uploads/students/9019/doc3_1758477473_5369.pdf', 'uploads/students/9019/doc4_1758477473_8969.pdf', NULL, NULL, NULL, NULL, '9307856854', 'House wife', '', '', '9307856854', 'Business', '', '', 'Plot 18, Ayodhya Colony kalamba ring road', '> 50,000 Rs', 'Daughter', 'paying', 'Admitted', '2025-09-21 17:57:53', 7, 1, 8, '2025-2026'),
(9352, 'BACHELOR OF COMPUTER APPLICATIONS - BCA Part 3', 'Miss.', 'Vaishnavi', 'Female', NULL, NULL, 'Kalyani', 'Mahendra ', 'Kangralkar', 'Plot 18, Ayodhya Colony kalamba ring road', 'Kolhapur', 'Karveer', 'Karveer', 'Plot 18, Ayodhya Colony kalamba ring road', '09307 85685', '79723 78977', 'Kolhapur', 'Karveer', 'Karveer', 'Kolhapur', 'Maharashtra', '2006-10-16', 'Sixteenth October Two Thousand Six', 18, 'No', 'Single', 'Hindu', 'Maratha', 'OPEN', '4738 0904 4051', 'OSHPK9353K', 'A+', 'shrinivaskangralkar8055@gmail.com', 'shrinivaskangralkar8055@gmail.com', 'Marathi', 'INDIA', 'Student', 'No', '5545450000', 'Yes', 'Yes', 'Yes', '{\"compulsory\":[\"31\",\"32\",\"33\",\"34\",\"35\",\"36\"],\"optional\":[\"37\"]}', 'Bank of Maharashtra', 'SBIN0003456', '65131351312321', 'Yes', 'SSC', 'SSC', '125689', 500, 87.00, 'Mahaveer English School', 'English', 87, 'Maths-1', 87, 'Maths-2', 87, 'Marathi ', 87, 'Hindi', 87, 'Science', 88, '', 0, '', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '9307856854', 'House wife', '', '', '9307856854', 'Business', '', '', 'Plot 18, Ayodhya Colony kalamba ring road', '> 50,000 Rs', 'Daughter', 'paying', 'Admitted', '2025-09-22 06:58:48', 6, 1, 7, '2025-2026'),
(9464, 'BACHELOR OF COMPUTER APPLICATIONS - BCA Part 2', 'Miss.', 'Vaishnavi', 'Female', NULL, NULL, 'Kalyani', 'Mahendra ', 'Kangralkar', 'Plot 18, Ayodhya Colony kalamba ring road', 'Kolhapur', 'Karveer', 'Karveer', 'Plot 18, Ayodhya Colony kalamba ring road', '09307 85685', '79723 78977', 'Kolhapur', 'Karveer', 'Karveer', 'Kolhapur', 'Maharashtra', '2006-10-16', 'Sixteenth October Two Thousand Six', 18, 'No', 'Single', 'Hindu', 'Maratha', 'OPEN', '4738 0904 4051', 'OSHPK9353K', 'A+', 'shrinivaskangralkar8055@gmail.com', 'shrinivaskangralkar8055@gmail.com', 'Marathi', 'INDIA', 'Student', 'No', '5545450000', 'Yes', 'Yes', 'Yes', '{\"compulsory\":[\"17\",\"18\",\"19\",\"20\",\"21\",\"22\",\"62\"],\"optional\":[\"23\"]}', 'Bank of Maharashtra', 'SBIN0003456', '65131351312321', 'Yes', 'SSC', 'SSC', '125689', 500, 87.00, 'Mahaveer English School', 'English', 87, 'Maths-1', 87, 'Maths-2', 87, 'Marathi ', 87, 'Hindi', 87, 'Science', 88, '', 0, '', 0, 'uploads/students/9464/doc1_1758477899_1899.pdf', 'uploads/students/9464/doc2_1758477899_7252.pdf', 'uploads/students/9464/doc3_1758477899_8010.pdf', 'uploads/students/9464/doc4_1758477899_9259.pdf', NULL, NULL, NULL, NULL, '9307856854', 'House wife', '', '', '9307856854', 'Business', '', '', 'Plot 18, Ayodhya Colony kalamba ring road', '> 50,000 Rs', 'Daughter', 'paying', 'Admitted', '2025-09-21 18:04:59', 7, 1, 8, '2025-2026');

-- --------------------------------------------------------

--
-- Table structure for table `student_subjects`
--

CREATE TABLE `student_subjects` (
  `ss_id` int(11) NOT NULL,
  `stu_id` int(11) NOT NULL,
  `cls_id` int(11) NOT NULL,
  `subj_data` longtext NOT NULL,
  `current_sem` int(11) NOT NULL,
  `status` enum('in_progress','completed','left') DEFAULT 'in_progress',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_subjects`
--

INSERT INTO `student_subjects` (`ss_id`, `stu_id`, `cls_id`, `subj_data`, `current_sem`, `status`, `created_at`) VALUES
(11, 2025100001, 35, '{\"current_sem\":1,\"status\":\"in_progress\",\"sem1\":{\"regular\":[1,2,3,4,5,6,7,8],\"backlog\":[]},\"sem2\":{\"regular\":[],\"backlog\":[]},\"sem3\":{\"regular\":[],\"backlog\":[]},\"sem4\":{\"regular\":[],\"backlog\":[]},\"sem5\":{\"regular\":[],\"backlog\":[]},\"sem6\":{\"regular\":[],\"backlog\":[]}}', 1, 'in_progress', '2025-09-21 17:58:31'),
(12, 2025100002, 35, '{\"current_sem\":1,\"status\":\"in_progress\",\"sem1\":{\"regular\":[1,2,3,4,5,6,7,8],\"backlog\":[]},\"sem2\":{\"regular\":[],\"backlog\":[]},\"sem3\":{\"regular\":[],\"backlog\":[]},\"sem4\":{\"regular\":[],\"backlog\":[]},\"sem5\":{\"regular\":[],\"backlog\":[]},\"sem6\":{\"regular\":[],\"backlog\":[]}}', 1, 'in_progress', '2025-09-21 17:59:45'),
(13, 2025100003, 35, '{\"current_sem\":0,\"status\":\"in_progress\",\"sem1\":{\"regular\":[],\"backlog\":[]},\"sem2\":{\"regular\":[],\"backlog\":[]},\"sem3\":{\"regular\":[],\"backlog\":[]},\"sem4\":{\"regular\":[],\"backlog\":[]},\"sem5\":{\"regular\":[],\"backlog\":[]},\"sem6\":{\"regular\":[],\"backlog\":[]}}', 0, 'in_progress', '2025-09-21 18:05:25'),
(14, 2025100004, 35, '{\"current_sem\":3,\"status\":\"in_progress\",\"sem1\":{\"regular\":[],\"backlog\":[]},\"sem2\":{\"regular\":[],\"backlog\":[]},\"sem3\":{\"regular\":[17,18,19,20,21,22,23,62],\"backlog\":[]},\"sem4\":{\"regular\":[],\"backlog\":[]},\"sem5\":{\"regular\":[],\"backlog\":[]},\"sem6\":{\"regular\":[],\"backlog\":[]}}', 3, 'in_progress', '2025-09-21 18:21:19'),
(15, 2025100005, 35, '{\"current_sem\":1,\"status\":\"in_progress\",\"sem1\":{\"regular\":[\"1\",\"2\",\"8\"],\"backlog\":[]},\"sem2\":{\"regular\":[],\"backlog\":[]},\"sem3\":{\"regular\":[],\"backlog\":[]},\"sem4\":{\"regular\":[],\"backlog\":[]},\"sem5\":{\"regular\":[],\"backlog\":[]},\"sem6\":{\"regular\":[],\"backlog\":[]}}', 1, 'in_progress', '2025-09-22 06:47:43'),
(16, 2025100006, 35, '{\"current_sem\":1,\"status\":\"in_progress\",\"sem1\":{\"regular\":[\"6\",\"7\",\"8\"],\"backlog\":[]},\"sem2\":{\"regular\":[],\"backlog\":[]},\"sem3\":{\"regular\":[],\"backlog\":[]},\"sem4\":{\"regular\":[],\"backlog\":[]},\"sem5\":{\"regular\":[],\"backlog\":[]},\"sem6\":{\"regular\":[],\"backlog\":[]}}', 1, 'in_progress', '2025-09-22 06:49:58'),
(17, 2025100007, 35, '{\"current_sem\":3,\"status\":\"in_progress\",\"sem1\":{\"regular\":[],\"backlog\":[]},\"sem2\":{\"regular\":[],\"backlog\":[]},\"sem3\":{\"regular\":[\"17\",\"18\",\"19\",\"20\",\"21\",\"22\",\"62\",\"23\"],\"backlog\":[]},\"sem4\":{\"regular\":[],\"backlog\":[]},\"sem5\":{\"regular\":[],\"backlog\":[]},\"sem6\":{\"regular\":[],\"backlog\":[]}}', 3, 'in_progress', '2025-09-22 06:53:59'),
(18, 2025100008, 35, '{\"current_sem\":5,\"status\":\"in_progress\",\"sem1\":{\"regular\":[],\"backlog\":[]},\"sem2\":{\"regular\":[],\"backlog\":[]},\"sem3\":{\"regular\":[],\"backlog\":[]},\"sem4\":{\"regular\":[],\"backlog\":[]},\"sem5\":{\"regular\":[\"31\",\"32\",\"33\",\"34\",\"35\",\"36\",\"37\"],\"backlog\":[]},\"sem6\":{\"regular\":[],\"backlog\":[]}}', 5, 'in_progress', '2025-09-22 06:59:07');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `sub_id` int(11) NOT NULL,
  `class_id` int(11) DEFAULT NULL,
  `sem` int(11) NOT NULL,
  `acad_yr` varchar(10) DEFAULT NULL,
  `int_cap` int(11) DEFAULT NULL,
  `class_name` varchar(255) DEFAULT NULL,
  `comp_sub` int(11) DEFAULT NULL,
  `sel_comp_sub` int(11) DEFAULT NULL,
  `op_sub` int(11) DEFAULT NULL,
  `sel_op_sub` int(11) DEFAULT NULL,
  `tot_sub` int(11) DEFAULT NULL,
  `sub_code` varchar(50) DEFAULT NULL,
  `sub_sh_nm` varchar(50) DEFAULT NULL,
  `sub_fl_nm` varchar(100) DEFAULT NULL,
  `sub_typ` varchar(20) DEFAULT NULL,
  `credit` int(20) DEFAULT NULL,
  `int_min_mrk` int(20) DEFAULT NULL,
  `int_max_mrk` int(20) DEFAULT NULL,
  `ext_min_mrk` int(20) DEFAULT NULL,
  `ext_max_mrk` int(20) DEFAULT NULL,
  `total` int(20) DEFAULT NULL,
  `type` enum('compulsory','optional') DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`sub_id`, `class_id`, `sem`, `acad_yr`, `int_cap`, `class_name`, `comp_sub`, `sel_comp_sub`, `op_sub`, `sel_op_sub`, `tot_sub`, `sub_code`, `sub_sh_nm`, `sub_fl_nm`, `sub_typ`, `credit`, `int_min_mrk`, `int_max_mrk`, `ext_min_mrk`, `ext_max_mrk`, `total`, `type`, `sort_order`, `status`) VALUES
(1, 35, 1, '2025-2026', 160, 'BACHELOR OF COMPUTER APPLICATIONS', 7, 2, 1, 1, 8, 'KCKBCA0101', 'COA', 'Funadamentals of Computers with office Automations', 'Theory+Lab', 4, 3, 10, 13, 40, 50, 'compulsory', 1, 'active'),
(2, 35, 1, '2025-2026', 160, 'BACHELOR OF COMPUTER APPLICATIONS', 7, 2, 1, 1, 8, 'KCKBCA0102', 'PUC-1', 'Programming Using C - 1', 'Theory+Lab', 4, 3, 10, 13, 40, 50, 'compulsory', 2, 'active'),
(3, 35, 1, '2025-2026', 160, 'BACHELOR OF COMPUTER APPLICATIONS', 7, 2, 1, 1, 8, 'KCKBCA0103', 'PM', 'Principles of Management', 'Theory', 2, 3, 10, 13, 40, 50, 'compulsory', 3, 'active'),
(4, 35, 1, '2025-2026', 160, 'BACHELOR OF COMPUTER APPLICATIONS', 7, 2, 1, 1, 8, 'KCKBCA0104', 'BC-1', 'Business Communication - 1', 'Theory', 2, 3, 10, 13, 40, 50, 'compulsory', 4, 'active'),
(5, 35, 1, '2025-2026', 160, 'BACHELOR OF COMPUTER APPLICATIONS', 7, 2, 1, 1, 8, 'KCKBCA0105', 'FA-1', 'Financial Accounting With Tally - 1', 'Theory', 2, 3, 10, 13, 40, 50, 'compulsory', 5, 'active'),
(6, 35, 1, '2025-2026', 160, 'BACHELOR OF COMPUTER APPLICATIONS', 7, 2, 1, 1, 8, 'KCKBCA0106', 'M-1', 'Maths - 1', 'Theory', 2, 3, 10, 13, 40, 50, 'compulsory', 6, 'active'),
(7, 35, 1, '2025-2026', 160, 'BACHELOR OF COMPUTER APPLICATIONS', 7, 2, 1, 1, 8, 'KCKBCA0107', 'M-2', 'Maths - 2', 'Theory', 2, 3, 10, 13, 40, 50, 'compulsory', 7, 'active'),
(8, 35, 1, '2025-2026', 160, 'BACHELOR OF COMPUTER APPLICATIONS', 7, 2, 1, 1, 8, 'KCKBCA0108', 'IKS', 'Indian Knowledge System', 'Theory', 2, 3, 10, 13, 40, 50, 'optional', 8, 'active'),
(9, 35, 2, '2025-2026', 160, 'BACHELOR OF COMPUTER APPLICATIONS', 7, 1, 1, 1, 8, 'KCKBCA0201', 'PUC-2', 'Introduction to Programming Using C - 2', 'Theory+Lab', 4, 3, 10, 13, 40, 50, 'compulsory', 1, 'active'),
(10, 35, 2, '2025-2026', 160, 'BACHELOR OF COMPUTER APPLICATIONS', 7, 1, 1, 1, 8, 'KCKBCA0202', 'WT', 'Web Technologies', 'Theory+Lab', 4, 3, 10, 13, 40, 50, 'compulsory', 2, 'active'),
(11, 35, 2, '2025-2026', 160, 'BACHELOR OF COMPUTER APPLICATIONS', 7, 1, 1, 1, 8, 'KCKBCA0203', 'FA-2', 'Financial Accounting With Tally - 2', 'Theory', 2, 3, 10, 13, 40, 50, 'compulsory', 3, 'active'),
(12, 35, 2, '2025-2026', 160, 'BACHELOR OF COMPUTER APPLICATIONS', 7, 1, 1, 1, 8, 'KCKBCA0204', 'BC-2', 'Business Communication - 2', 'Theory', 2, 3, 10, 13, 40, 50, 'compulsory', 4, 'active'),
(13, 35, 2, '2025-2026', 160, 'BACHELOR OF COMPUTER APPLICATIONS', 7, 1, 1, 1, 8, 'KCKBCA0205', 'PM-2', 'Principles of Management', 'Theory', 2, 3, 10, 13, 40, 50, 'compulsory', 5, 'active'),
(14, 35, 2, '2025-2026', 160, 'BACHELOR OF COMPUTER APPLICATIONS', 7, 1, 1, 1, 8, 'KCKBCA0206', 'STAT-1', 'Statistics - 1', 'Theory', 2, 3, 10, 13, 40, 50, 'compulsory', 6, 'active'),
(16, 35, 2, '2025-2026', 160, 'BACHELOR OF COMPUTER APPLICATIONS', 7, 1, 1, 1, 8, 'KCKBCA0208', 'PD', 'Personality Development', 'Theory', 2, 3, 10, 13, 40, 50, 'optional', 8, 'active'),
(17, 35, 3, '2025-2026', 160, 'BACHELOR OF COMPUTER APPLICATIONS', 7, 7, 1, 1, 8, 'KCKBCA0301', 'OOP', 'Object Oriented Programming Using C++', 'Theory+Lab', 4, 3, 10, 13, 40, 50, 'compulsory', 1, 'active'),
(18, 35, 3, '2025-2026', 160, 'BACHELOR OF COMPUTER APPLICATIONS', 7, 7, 1, 1, 8, 'KCKBCA0302', 'DBMS', 'DBMS', 'Theory+Lab', 4, 3, 10, 13, 40, 50, 'compulsory', 2, 'active'),
(19, 35, 3, '2025-2026', 160, 'BACHELOR OF COMPUTER APPLICATIONS', 7, 7, 1, 1, 8, 'KCKBCA0303', 'HRM', 'Human Resource Management', 'Theory', 2, 3, 10, 13, 40, 50, 'compulsory', 3, 'active'),
(20, 35, 3, '2025-2026', 160, 'BACHELOR OF COMPUTER APPLICATIONS', 7, 7, 1, 1, 8, 'KCKBCA0304', 'BC-3', 'Business Communication - 3', 'Theory', 2, 3, 10, 13, 40, 50, 'compulsory', 4, 'active'),
(21, 35, 3, '2025-2026', 160, 'BACHELOR OF COMPUTER APPLICATIONS', 7, 7, 1, 1, 8, 'KCKBCA0305', 'PHP-1', 'PHP - 1', 'Theory', 2, 3, 10, 13, 40, 50, 'compulsory', 5, 'active'),
(22, 35, 3, '2025-2026', 160, 'BACHELOR OF COMPUTER APPLICATIONS', 7, 7, 1, 1, 8, 'KCKBCA0306', 'PHP-2', 'PHP - 2', 'Theory', 2, 3, 10, 13, 40, 50, 'compulsory', 6, 'active'),
(23, 35, 3, '2025-2026', 160, 'BACHELOR OF COMPUTER APPLICATIONS', 7, 7, 1, 1, 8, 'KCKBCA0307', 'DEMO', 'Democracy', 'Theory', 2, 3, 10, 13, 40, 50, 'optional', 7, 'active'),
(24, 35, 4, '2025-2026', 160, 'BACHELOR OF COMPUTER APPLICATIONS', 6, 6, 1, 1, 7, 'KCKBCA0401', 'RDBMS', 'RDBMS', 'Theory+Lab', 4, 3, 10, 13, 40, 50, 'compulsory', 1, 'active'),
(25, 35, 4, '2025-2026', 160, 'BACHELOR OF COMPUTER APPLICATIONS', 6, 6, 1, 1, 7, 'KCKBCA0402', 'DS', 'Data Structures', 'Theory+Lab', 4, 3, 10, 13, 40, 50, 'compulsory', 2, 'active'),
(26, 35, 4, '2025-2026', 160, 'BACHELOR OF COMPUTER APPLICATIONS', 6, 6, 1, 1, 7, 'KCKBCA0403', 'BC-4', 'Business Communication - 4', 'Theory', 2, 3, 10, 13, 40, 50, 'compulsory', 3, 'active'),
(27, 35, 4, '2025-2026', 160, 'BACHELOR OF COMPUTER APPLICATIONS', 6, 6, 1, 1, 7, 'KCKBCA0404', 'EVS', 'Environmental Science', 'Theory', 2, 3, 10, 13, 40, 50, 'compulsory', 4, 'active'),
(28, 35, 4, '2025-2026', 160, 'BACHELOR OF COMPUTER APPLICATIONS', 6, 6, 1, 1, 7, 'KCKBCA0405', 'AI-1', 'Artificial Intelligence - 1', 'Theory', 2, 3, 10, 13, 40, 50, 'compulsory', 5, 'active'),
(29, 35, 4, '2025-2026', 160, 'BACHELOR OF COMPUTER APPLICATIONS', 6, 6, 1, 1, 7, 'KCKBCA0406', 'AI-2', 'Artificial Intelligence - 2', 'Theory', 2, 3, 10, 13, 40, 50, 'compulsory', 6, 'active'),
(30, 35, 4, '2025-2026', 160, 'BACHELOR OF COMPUTER APPLICATIONS', 6, 6, 1, 1, 7, 'KCKBCA0407', 'PD', 'Personality Development', 'Theory', 2, 3, 10, 13, 40, 50, 'optional', 7, 'active'),
(31, 35, 5, '2025-2026', 160, 'BACHELOR OF COMPUTER APPLICATIONS', 6, 6, 1, 1, 7, 'KCKBCA0501', 'JP', 'Java Programming', 'Theory+Lab', 4, 3, 10, 13, 40, 50, 'compulsory', 1, 'active'),
(32, 35, 5, '2025-2026', 160, 'BACHELOR OF COMPUTER APPLICATIONS', 6, 6, 1, 1, 7, 'KCKBCA0502', 'CNI', 'Computer Networks & Internet', 'Theory', 2, 3, 10, 13, 40, 50, 'compulsory', 2, 'active'),
(33, 35, 5, '2025-2026', 160, 'BACHELOR OF COMPUTER APPLICATIONS', 6, 6, 1, 1, 7, 'KCKBCA0503', 'ASP.NET', 'ASP.NET using C# - 1', 'Theory', 2, 3, 10, 13, 40, 50, 'compulsory', 3, 'active'),
(34, 35, 5, '2025-2026', 160, 'BACHELOR OF COMPUTER APPLICATIONS', 6, 6, 1, 1, 7, 'KCKBCA0504', 'CC', 'Cloud Computing', 'Theory', 2, 3, 10, 13, 40, 50, 'compulsory', 4, 'active'),
(35, 35, 5, '2025-2026', 160, 'BACHELOR OF COMPUTER APPLICATIONS', 6, 6, 1, 1, 7, 'KCKBCA0505', 'Py/IoT', 'Python / IoT', 'Theory', 2, 3, 10, 13, 40, 50, 'compulsory', 5, 'active'),
(36, 35, 5, '2025-2026', 160, 'BACHELOR OF COMPUTER APPLICATIONS', 6, 6, 1, 1, 7, 'KCKBCA0506', 'FP', 'Field Project', 'Theory', 2, 3, 10, 13, 40, 50, 'compulsory', 6, 'active'),
(37, 35, 5, '2025-2026', 160, 'BACHELOR OF COMPUTER APPLICATIONS', 6, 6, 1, 1, 7, 'KCKBCA0507', 'M-Com', 'M-Commerce', 'Theory', 2, 3, 10, 13, 40, 50, 'optional', 7, 'active'),
(38, 35, 6, '2025-2026', 160, 'BACHELOR OF COMPUTER APPLICATIONS', 6, 6, 1, 1, 7, 'KCKBCA0601', 'LINUX', 'Linux', 'Theory+Lab', 4, 3, 10, 13, 40, 50, 'compulsory', 1, 'active'),
(39, 35, 6, '2025-2026', 160, 'BACHELOR OF COMPUTER APPLICATIONS', 6, 6, 1, 1, 7, 'KCKBCA0602', 'DW,DM', 'Data Wearhousing , Data Mining', 'Theory', 2, 3, 10, 13, 40, 50, 'compulsory', 2, 'active'),
(40, 35, 6, '2025-2026', 160, 'BACHELOR OF COMPUTER APPLICATIONS', 6, 6, 1, 1, 7, 'KCKBCA0603', 'ASP.NET-2', 'ASP.NET using C# - 2', 'Theory', 2, 3, 10, 13, 40, 50, 'compulsory', 3, 'active'),
(41, 35, 6, '2025-2026', 160, 'BACHELOR OF COMPUTER APPLICATIONS', 6, 6, 1, 1, 7, 'KCKBCA0604', 'IT-S', 'IT - Security', 'Theory', 2, 3, 10, 13, 40, 50, 'compulsory', 4, 'active'),
(42, 35, 6, '2025-2026', 160, 'BACHELOR OF COMPUTER APPLICATIONS', 6, 6, 1, 1, 7, 'KCKBCA0605', 'AP/RP', 'Android Programming / R Programming', 'Theory', 2, 3, 10, 13, 40, 50, 'compulsory', 5, 'active'),
(43, 35, 6, '2025-2026', 160, 'BACHELOR OF COMPUTER APPLICATIONS', 6, 6, 1, 1, 7, 'KCKBCA0606', 'MP', 'Major Project', 'Theory', 2, 3, 10, 13, 40, 50, 'compulsory', 6, 'active'),
(44, 35, 6, '2025-2026', 160, 'BACHELOR OF COMPUTER APPLICATIONS', 6, 6, 1, 1, 7, 'KCKBCA0607', 'DM', 'Digital Marketing', 'Theory', 2, 3, 10, 13, 40, 50, 'optional', 7, 'active'),
(47, 35, 2, '2025-2026', 160, 'BACHELOR OF COMPUTER APPLICATIONS', 7, 1, 1, 1, 8, 'KCKBCA0207', 'STAT-2', 'Statistics - 2', 'Theory', 2, 3, 10, 13, 40, 50, 'compulsory', 9, 'active'),
(48, 32, 1, '2025-2026', 210, 'SCIENCE : PHYSICS, CHEMISTRY, MATHS, BIOLOGY', 5, 1, 2, 1, 7, 'KCK1101', 'CHEM', 'Chemistry', 'Theory', 2, 3, 10, 13, 40, 50, 'compulsory', 1, 'active'),
(49, 32, 1, '2025-2026', 210, 'SCIENCE : PHYSICS, CHEMISTRY, MATHS, BIOLOGY', 5, 1, 2, 1, 7, 'KCK1102', 'PHY', 'Physics', 'Theory', 2, 3, 10, 13, 40, 50, 'compulsory', 2, 'active'),
(50, 32, 1, '2025-2026', 210, 'SCIENCE : PHYSICS, CHEMISTRY, MATHS, BIOLOGY', 5, 1, 2, 1, 7, 'KCK1103', 'MATH', 'Maths', 'Theory', 2, 3, 10, 13, 40, 50, 'compulsory', 3, 'active'),
(51, 32, 1, '2025-2026', 210, 'SCIENCE : PHYSICS, CHEMISTRY, MATHS, BIOLOGY', 5, 1, 2, 1, 7, 'KCK1104', 'BIO', 'Biology', 'Theory', 2, 3, 10, 13, 40, 50, 'compulsory', 4, 'active'),
(52, 32, 1, '2025-2026', 210, 'SCIENCE : PHYSICS, CHEMISTRY, MATHS, BIOLOGY', 5, 1, 2, 1, 7, 'KCK1105', 'ENG', 'English', 'Theory', 2, 3, 10, 13, 40, 50, 'compulsory', 5, 'active'),
(53, 32, 1, '2025-2026', 210, 'SCIENCE : PHYSICS, CHEMISTRY, MATHS, BIOLOGY', 5, 1, 2, 1, 7, 'KCK1106', 'CS', 'Computer Science', 'Theory', 2, 3, 10, 13, 40, 50, 'optional', 6, 'active'),
(54, 32, 1, '2025-2026', 210, 'SCIENCE : PHYSICS, CHEMISTRY, MATHS, BIOLOGY', 5, 1, 2, 1, 7, 'KCK1107', 'EVS', 'Environmental Science', 'Theory', 2, 3, 10, 13, 40, 50, 'optional', 7, 'active'),
(55, 32, 2, '2025-2026', 210, 'SCIENCE : PHYSICS, CHEMISTRY, MATHS, BIOLOGY', 5, 1, 2, 1, 7, 'KCK1201', 'CHEM', 'Chemistry', 'Theory', 2, 3, 10, 13, 40, 50, 'compulsory', 1, 'active'),
(56, 32, 2, '2025-2026', 210, 'SCIENCE : PHYSICS, CHEMISTRY, MATHS, BIOLOGY', 5, 1, 2, 1, 7, 'KCK1202', 'PHY', 'Physics', 'Theory', 2, 3, 10, 13, 40, 50, 'compulsory', 2, 'active'),
(57, 32, 2, '2025-2026', 210, 'SCIENCE : PHYSICS, CHEMISTRY, MATHS, BIOLOGY', 5, 1, 2, 1, 7, 'KCK1203', 'MATH', 'Maths', 'Theory', 2, 3, 10, 13, 40, 50, 'compulsory', 3, 'active'),
(58, 32, 2, '2025-2026', 210, 'SCIENCE : PHYSICS, CHEMISTRY, MATHS, BIOLOGY', 5, 1, 2, 1, 7, 'KCK1204', 'BIO', 'Biology', 'Theory', 2, 3, 10, 13, 40, 50, 'compulsory', 4, 'active'),
(59, 32, 2, '2025-2026', 210, 'SCIENCE : PHYSICS, CHEMISTRY, MATHS, BIOLOGY', 5, 1, 2, 1, 7, 'KCK1205', 'ENG', 'English', 'Theory', 2, 3, 10, 13, 40, 50, 'compulsory', 5, 'active'),
(60, 32, 2, '2025-2026', 210, 'SCIENCE : PHYSICS, CHEMISTRY, MATHS, BIOLOGY', 5, 1, 2, 1, 7, 'KCK1206', 'CS', 'Computer Science', 'Theory', 2, 3, 10, 13, 40, 50, 'optional', 6, 'active'),
(61, 32, 2, '2025-2026', 210, 'SCIENCE : PHYSICS, CHEMISTRY, MATHS, BIOLOGY', 5, 1, 2, 1, 7, 'KCK1207', 'EVS', 'Envionmental Science', 'Theory', 2, 3, 10, 13, 40, 50, 'optional', 7, 'active'),
(62, 35, 3, '2025-2026', 160, 'BACHELOR OF COMPUTER APPLICATIONS', 7, 7, 1, 1, 8, 'KCKBCA0308', 'CC', 'Compulsory Subject', 'Theory', 2, 3, 10, 13, 40, 50, 'compulsory', 8, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `subject_summary`
--

CREATE TABLE `subject_summary` (
  `class_id` int(11) NOT NULL,
  `sem` int(11) NOT NULL,
  `comp_sub` int(11) NOT NULL,
  `sel_comp_sub` int(11) DEFAULT NULL,
  `op_sub` int(11) NOT NULL,
  `sel_op_sub` int(11) DEFAULT NULL,
  `tot_sub` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subject_summary`
--

INSERT INTO `subject_summary` (`class_id`, `sem`, `comp_sub`, `sel_comp_sub`, `op_sub`, `sel_op_sub`, `tot_sub`) VALUES
(32, 1, 5, 1, 2, 1, 7),
(32, 2, 5, 1, 2, 1, 7),
(35, 1, 7, 2, 1, 1, 8),
(35, 2, 7, 1, 1, 1, 8),
(35, 3, 7, 7, 1, 1, 8),
(35, 4, 6, 6, 1, 1, 7),
(35, 5, 6, 6, 1, 1, 7),
(35, 6, 6, 6, 1, 1, 7);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admts`
--
ALTER TABLE `admts`
  ADD PRIMARY KEY (`r_id`),
  ADD UNIQUE KEY `prn_no` (`prn_no`);

--
-- Indexes for table `caste_m`
--
ALTER TABLE `caste_m`
  ADD PRIMARY KEY (`caste_id`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`cls_id`);

--
-- Indexes for table `feecls`
--
ALTER TABLE `feecls`
  ADD PRIMARY KEY (`term_id`),
  ADD KEY `cls_id` (`cls_id`);

--
-- Indexes for table `feestru`
--
ALTER TABLE `feestru`
  ADD PRIMARY KEY (`fee_id`),
  ADD KEY `fk_term` (`term_id`);

--
-- Indexes for table `religion_m`
--
ALTER TABLE `religion_m`
  ADD PRIMARY KEY (`rel_id`);

--
-- Indexes for table `student_registration`
--
ALTER TABLE `student_registration`
  ADD PRIMARY KEY (`r_id`);

--
-- Indexes for table `student_subjects`
--
ALTER TABLE `student_subjects`
  ADD PRIMARY KEY (`ss_id`),
  ADD KEY `stu_id` (`stu_id`),
  ADD KEY `cls_id` (`cls_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`sub_id`),
  ADD KEY `idx_class_id` (`class_id`);

--
-- Indexes for table `subject_summary`
--
ALTER TABLE `subject_summary`
  ADD PRIMARY KEY (`class_id`,`sem`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `caste_m`
--
ALTER TABLE `caste_m`
  MODIFY `caste_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `cls_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `feecls`
--
ALTER TABLE `feecls`
  MODIFY `term_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `feestru`
--
ALTER TABLE `feestru`
  MODIFY `fee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=172;

--
-- AUTO_INCREMENT for table `religion_m`
--
ALTER TABLE `religion_m`
  MODIFY `rel_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `student_subjects`
--
ALTER TABLE `student_subjects`
  MODIFY `ss_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `sub_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `feecls`
--
ALTER TABLE `feecls`
  ADD CONSTRAINT `feecls_ibfk_1` FOREIGN KEY (`cls_id`) REFERENCES `classes` (`cls_id`) ON DELETE CASCADE;

--
-- Constraints for table `feestru`
--
ALTER TABLE `feestru`
  ADD CONSTRAINT `fk_term` FOREIGN KEY (`term_id`) REFERENCES `feecls` (`term_id`) ON DELETE CASCADE;

--
-- Constraints for table `student_subjects`
--
ALTER TABLE `student_subjects`
  ADD CONSTRAINT `student_subjects_ibfk_1` FOREIGN KEY (`stu_id`) REFERENCES `admts` (`prn_no`),
  ADD CONSTRAINT `student_subjects_ibfk_2` FOREIGN KEY (`cls_id`) REFERENCES `classes` (`cls_id`);

--
-- Constraints for table `subjects`
--
ALTER TABLE `subjects`
  ADD CONSTRAINT `fk_subjects_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`cls_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
