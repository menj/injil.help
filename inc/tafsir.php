<?php
/**
 * The classical commentaries (tafsīr) on the Qur'anic passages about Jesus,
 * translated in full: Ṭabarī, Baghawī, Qurṭubī, Ibn Kathīr, Tanwīr al-Miqbās,
 * Jalālayn, al-Durr al-Manthūr and Saʿdī on 3:33-64, 4:155-159, 5:110-120,
 * 19:16-37, 21:91, 23:50, 43:57-65, 57:27 and 61:6.
 *
 * The translations are data, one JSON file per work in inc/data/tafsir/, each
 * holding "entries": the commentary on one verse range as a list of paragraphs in
 * the compact page markup. From them this file builds the pages under
 * /quran/commentaries/ (an index, one page per verse group, and one page or set
 * of pages per work and group) and the "From the commentaries" blocks woven into
 * the sections of The Gospel According to Islam, so that every passage of the
 * gospel that rests on a Qur'anic verse carries what the commentators said of it.
 *
 * @package injil-help
 */

defined( 'ABSPATH' ) || exit;

/** The works in the order they are shown: by the death of the author or compiler. */
function injil_help_tafsir_works() {
	return array(
		'tabari'           => array(
			'short'  => 'al-Ṭabarī',
			'title'  => 'Jāmiʿ al-bayān ʿan taʾwīl āy al-Qurʾān',
			'author' => 'Abū Jaʿfar Muḥammad ibn Jarīr al-Ṭabarī',
			'died'   => '310 AH / 923 CE',
			'about'  => 'The earliest of the great commentaries to survive whole. Al-Ṭabarī sets out every opinion he knows on a verse with its chain of transmission, then rules between them. His reports on 4:157 are the oldest record of how the substitution at the cross was understood.',
			'source' => 'tafsir.app, src=tabari',
		),
		'baghawi'          => array(
			'short'  => 'al-Baghawī',
			'title'  => 'Maʿālim al-tanzīl',
			'author' => 'Abū Muḥammad al-Ḥusayn ibn Masʿūd al-Baghawī',
			'died'   => '516 AH / 1122 CE',
			'about'  => 'A commentary of the school of ḥadīth, valued for its restraint; it keeps the early reports and little else.',
			'source' => 'tafsir.app, src=baghawi',
		),
		'qurtubi'          => array(
			'short'  => 'al-Qurṭubī',
			'title'  => 'al-Jāmiʿ li-aḥkām al-Qurʾān',
			'author' => 'Abū ʿAbd Allāh Muḥammad ibn Aḥmad al-Qurṭubī',
			'died'   => '671 AH / 1273 CE',
			'about'  => 'The commentary of the Andalusian jurist, which takes each verse through its legal and grammatical questions as well as its narrative.',
			'source' => 'tafsir.app, src=qurtubi',
		),
		'ibn-katheer'      => array(
			'short'  => 'Ibn Kathīr',
			'title'  => 'Tafsīr al-Qurʾān al-ʿAẓīm',
			'author' => 'Abū al-Fidāʾ Ismāʿīl ibn ʿUmar ibn Kathīr',
			'died'   => '774 AH / 1373 CE',
			'about'  => 'The most read of the commentaries by ḥadīth, which explains the Qur’an by the Qur’an, then by the Prophet’s words, then by the Companions, and weighs the chains it cites.',
			'source' => 'tafsir.app, src=ibn-katheer',
		),
		'tanwir'           => array(
			'short'  => 'Tanwīr al-Miqbās',
			'title'  => 'Tanwīr al-miqbās min tafsīr Ibn ʿAbbās',
			'author' => 'attributed to ʿAbd Allāh ibn ʿAbbās; compiled by Majd al-Dīn al-Fīrūzābādī',
			'died'   => 'compiler d. 817 AH / 1414 CE',
			'about'  => 'A running gloss, phrase by phrase, transmitted under the name of the Prophet’s cousin Ibn ʿAbbās through a chain (al-Kalbī from Abū Ṣāliḥ) that Muslim scholars have long judged weak; it is read as a witness to early Kufan exegesis rather than as Ibn ʿAbbās’s own words.',
			'source' => 'shamela.ws, book 12877',
		),
		'jalalayn'         => array(
			'short'  => 'al-Jalālayn',
			'title'  => 'Tafsīr al-Jalālayn',
			'author' => 'Jalāl al-Dīn al-Maḥallī and Jalāl al-Dīn al-Suyūṭī',
			'died'   => '864 AH / 1459 CE and 911 AH / 1505 CE',
			'about'  => 'The two Jalāls’ gloss, a sentence or two to a verse, which became the handbook commentary of the Muslim world.',
			'source' => 'tafsir.app, src=jalalayn',
		),
		'aldur-almanthoor' => array(
			'short'  => 'al-Durr al-Manthūr',
			'title'  => 'al-Durr al-manthūr fī al-tafsīr bi-l-maʾthūr',
			'author' => 'Jalāl al-Dīn al-Suyūṭī',
			'died'   => '911 AH / 1505 CE',
			'about'  => 'Al-Suyūṭī’s collection of every transmitted report on each verse, drawn from the ḥadīth collections and the early commentaries, many of them otherwise lost.',
			'source' => 'tafsir.app, src=aldur-almanthoor',
		),
		'saadi'            => array(
			'short'  => 'al-Saʿdī',
			'title'  => 'Taysīr al-Karīm al-Raḥmān fī tafsīr kalām al-Mannān',
			'author' => 'ʿAbd al-Raḥmān ibn Nāṣir al-Saʿdī',
			'died'   => '1376 AH / 1956 CE',
			'about'  => 'A modern commentary in plain Arabic that states the sense of each verse and the lesson drawn from it, without chains or digressions.',
			'source' => 'tafsir.app, src=saadi',
		),
	);
}

/** The verse groups, in Qur'anic order, with the gospel sections each feeds. */
function injil_help_tafsir_groups() {
	return array(
		'al-imran-33-64'    => array(
			'surah'  => 3,
			'from'   => 33,
			'to'     => 64,
			'name'   => 'Āl ʿImrān',
			'title'  => 'The family of ʿImrān (3:33-64)',
			'intro'  => 'Mary’s mother and her vow, the birth of John to Zachariah, the annunciation to Mary, the signs Jesus was sent with, the disciples, the plot against him and his raising, the likeness of Jesus to Adam, the challenge to the delegation of Najrān, and the call to a common word.',
		),
		'an-nisa-155-159'   => array(
			'surah'  => 4,
			'from'   => 155,
			'to'     => 159,
			'name'   => 'al-Nisāʾ',
			'title'  => 'Neither killed nor crucified (4:155-159)',
			'intro'  => 'The breaking of the covenant, the charge against Mary, the claim to have killed the Messiah, the denial of it, the raising of Jesus to God, and the faith of the People of the Book in him before his death.',
		),
		'al-maidah-110-120' => array(
			'surah'  => 5,
			'from'   => 110,
			'to'     => 120,
			'name'   => 'al-Māʾidah',
			'title'  => 'God’s favours, the table and the questioning (5:110-120)',
			'intro'  => 'God recounts His favours to Jesus, the disciples ask for a table from heaven, and on the Day of Judgement God asks Jesus whether he told people to take him and his mother as gods.',
		),
		'maryam-16-37'      => array(
			'surah'  => 19,
			'from'   => 16,
			'to'     => 37,
			'name'   => 'Maryam',
			'title'  => 'The birth of Jesus (19:16-37)',
			'intro'  => 'Mary withdraws, the Spirit appears to her as a man, she conceives and gives birth under the palm, brings the child to her people, and he speaks from the cradle; then the verdict on those who dispute about him.',
		),
		'al-anbiya-91'      => array(
			'surah'  => 21,
			'from'   => 91,
			'to'     => 91,
			'name'   => 'al-Anbiyāʾ',
			'title'  => 'She who guarded her chastity (21:91)',
			'intro'  => 'Mary and her son made a sign for all peoples.',
		),
		'al-muminun-50'     => array(
			'surah'  => 23,
			'from'   => 50,
			'to'     => 50,
			'name'   => 'al-Muʾminūn',
			'title'  => 'The refuge on high ground (23:50)',
			'intro'  => 'The son of Mary and his mother given shelter on a height with a running spring.',
		),
		'az-zukhruf-57-65'  => array(
			'surah'  => 43,
			'from'   => 57,
			'to'     => 65,
			'name'   => 'al-Zukhruf',
			'title'  => 'The son of Mary as an example (43:57-65)',
			'intro'  => 'The Meccans’ dispute over Jesus, his standing as a servant and a sign of the Hour, his call to the straight path, and the sects that differed about him.',
		),
		'al-hadid-27'       => array(
			'surah'  => 57,
			'from'   => 27,
			'to'     => 27,
			'name'   => 'al-Ḥadīd',
			'title'  => 'Compassion, mercy and monasticism (57:27)',
			'intro'  => 'The followers of Jesus, the compassion God put in their hearts, and the monasticism they invented.',
		),
		'as-saff-6'         => array(
			'surah'  => 61,
			'from'   => 6,
			'to'     => 6,
			'name'   => 'al-Ṣaff',
			'title'  => 'The good news of Aḥmad (61:6)',
			'intro'  => 'Jesus confirms the Torah and announces a messenger to come after him.',
		),
	);
}

/**
 * Which verse ranges each gospel section carries as "From the commentaries".
 * An entry of a commentary is placed in every section whose range it touches,
 * so a commentator who treats 4:155-159 together is read in full at the arrest,
 * the trial and the raising alike.
 */
function injil_help_tafsir_section_map() {
	return array(
		1  => array( array( 3, 33, 41 ) ),
		2  => array( array( 3, 42, 47 ), array( 19, 16, 33 ), array( 21, 91, 91 ), array( 23, 50, 50 ) ),
		3  => array( array( 3, 59, 63 ), array( 19, 34, 37 ) ),
		7  => array( array( 3, 52, 53 ) ),
		10 => array( array( 3, 50, 51 ) ),
		13 => array( array( 57, 27, 27 ) ),
		14 => array( array( 3, 48, 49 ), array( 5, 110, 110 ) ),
		16 => array( array( 5, 111, 115 ) ),
		21 => array( array( 3, 64, 64 ) ),
		22 => array( array( 43, 57, 65 ) ),
		24 => array( array( 61, 6, 6 ) ),
		25 => array( array( 3, 54, 54 ), array( 4, 155, 156 ) ),
		26 => array( array( 4, 157, 157 ) ),
		27 => array( array( 3, 55, 58 ), array( 4, 158, 159 ), array( 5, 116, 120 ) ),
	);
}

/** One work's data file: { "entries": [ { "surah", "from", "to", "arabic_words", "english_words", "paragraphs": [...] } ] }. */
function injil_help_tafsir_data( $work ) {
	static $cache = array();
	if ( isset( $cache[ $work ] ) ) {
		return $cache[ $work ];
	}
	$file    = get_stylesheet_directory() . '/inc/data/tafsir/' . $work . '.json';
	$entries = array();
	if ( is_readable( $file ) ) {
		$json = json_decode( (string) file_get_contents( $file ), true );
		foreach ( (array) ( $json['entries'] ?? array() ) as $e ) {
			if ( ! empty( $e['paragraphs'] ) && isset( $e['surah'], $e['from'], $e['to'] ) ) {
				$entries[] = $e;
			}
		}
	}
	usort(
		$entries,
		function ( $a, $b ) {
			return array( (int) $a['surah'], (int) $a['from'] ) <=> array( (int) $b['surah'], (int) $b['from'] );
		}
	);
	$cache[ $work ] = $entries;
	return $entries;
}

/** Entries of a work whose verse range touches surah:from-to. */
function injil_help_tafsir_entries( $work, $surah, $from, $to ) {
	$out = array();
	foreach ( injil_help_tafsir_data( $work ) as $e ) {
		if ( (int) $e['surah'] === (int) $surah && (int) $e['to'] >= (int) $from && (int) $e['from'] <= (int) $to ) {
			$out[] = $e;
		}
	}
	return $out;
}

/** "3:33" or "3:33-36". */
function injil_help_tafsir_ref( $e ) {
	return (int) $e['from'] === (int) $e['to'] ? $e['surah'] . ':' . $e['from'] : $e['surah'] . ':' . $e['from'] . '-' . $e['to'];
}

/** Words in a list of markup paragraphs, tags and footnotes stripped. */
function injil_help_tafsir_words( $paragraphs ) {
	$text = preg_replace( '/\{\{fn:.*?\}\}/s', '', implode( ' ', (array) $paragraphs ) );
	return str_word_count( wp_strip_all_tags( (string) $text ) );
}

/** The markup lines of one entry: a heading for the verse range, then its paragraphs. */
function injil_help_tafsir_entry_lines( $e, $level = '## ' ) {
	$lines   = array( $level . 'On ' . injil_help_tafsir_ref( $e ) );
	foreach ( (array) $e['paragraphs'] as $p ) {
		$lines[] = $p;
	}
	return $lines;
}

/** Whether any translation is on disk at all. */
function injil_help_tafsir_available() {
	foreach ( array_keys( injil_help_tafsir_works() ) as $w ) {
		if ( injil_help_tafsir_data( $w ) ) {
			return true;
		}
	}
	return false;
}

/** The note that closes every commentary page: what the text is and how to check it. */
function injil_help_tafsir_colophon( $work ) {
	$w = injil_help_tafsir_works()[ $work ];
	return sprintf(
		'This is a complete English rendering of %1$s on these verses, prepared for this website from the public-domain Arabic text (%2$s). Nothing is abridged: every opinion, every report with its chain of transmission, every verse of poetry and every grammatical note is translated. Qur’anic quotations are in bold; the translator’s own notes are confined to the numbered footnotes and identify names, terms and variant readings only. A second translator has not checked the rendering, and the Arabic is linked so that any passage can be compared. The modern editors’ apparatus is not part of the classical text and is not translated.',
		esc_html( $w['title'] ),
		esc_html( $w['source'] )
	);
}

/** Pages: the index, a page per verse group, and a page (or a numbered set) per work and group. */
function injil_help_tafsir_pages() {
	if ( ! injil_help_tafsir_available() ) {
		return array();
	}
	$works  = injil_help_tafsir_works();
	$groups = injil_help_tafsir_groups();
	$base   = 'quran/commentaries';
	$pages  = array();
	$limit  = 14000; // English words per page before a work's commentary on a group is split into numbered parts.

	// Totals for the index.
	$totals = array();
	foreach ( $works as $key => $w ) {
		$n = 0;
		foreach ( injil_help_tafsir_data( $key ) as $e ) {
			$n += injil_help_tafsir_words( $e['paragraphs'] );
		}
		$totals[ $key ] = $n;
	}

	$index = array(
		'The Qur’an speaks of Jesus, son of Mary, in some ninety verses, and for thirteen centuries Muslim scholars have written on each of them: what the words mean, what the Prophet and his Companions said about them, and what happened at the manger, in the temple, at the table and at the cross. Those commentaries are the Islamic record of the life of Jesus, and most of them have never been put into English. This part of the website gives them in full, verse by verse, for the passages that tell his story: his family and birth, his signs and his disciples, the plot against him, the cross and his raising, and the questioning on the Last Day.',
		'Eight works are translated, from the tenth century to the twentieth. They are not digests. Every opinion a commentator records is here, with the chain of transmission he gives for it, every verse of poetry he cites for a word’s meaning, and every grammatical argument he makes, because the reader who wants to know what Islam has said of Jesus should have the whole of it and not a selection made for him. The same texts are woven, passage by passage, into <a href="/gospel-according-to-islam/">The Gospel According to Islam</a>, so that the gospel and its commentary are read together.',
		'## The passages',
	);
	foreach ( $groups as $slug => $g ) {
		$index[] = sprintf( '- <strong><a href="/%1$s/%2$s/">%3$s</a>.</strong> %4$s', $base, $slug, $g['title'], $g['intro'] );
	}
	$index[] = '## The commentaries';
	foreach ( $works as $key => $w ) {
		if ( ! $totals[ $key ] ) {
			continue;
		}
		$index[] = sprintf( '- <strong>%1$s</strong>, <em>%2$s</em>, %3$s (d. %4$s). %5$s About %6$s words in English on these passages.', $w['short'], $w['title'], $w['author'], $w['died'], $w['about'], number_format_i18n( round( $totals[ $key ], -2 ) ) );
	}
	$index[] = '## How to read them';
	$index[] = 'Each commentary is given on its own page for each passage, in the order of the verses, under a heading for the verse or verses it treats. Qur’anic quotations within the commentary are in bold, with the reference where it can be identified. Where a commentator records several opinions, they are listed; where he cites a line of poetry for a word, it is set as a quotation; where he gives a chain of transmission, it is given in full, with “related to us” for <em>ḥaddathanā</em> and “from” for <em>ʿan</em>. The translator’s notes are numbered and identify names, places, terms and variant readings; they never summarise or replace the text. The classical commentators quote one another freely, so the reader will meet the same report more than once, sometimes with a different chain, and that is as it should be: it shows how the report travelled.';
	$index[] = 'The reports in these commentaries are of two kinds, and the commentators themselves say so. Some go back to the Prophet and are weighed by the rules of ḥadīth. Many more are what the early Muslims heard from converts from Judaism and Christianity, Kaʿb al-Aḥbār and Wahb ibn Munabbih above all, and are called <em>isrāʾīliyyāt</em>; Ibn Kathīr in particular marks them and warns against building doctrine on them. They are kept here because they are part of the record, and because several of them preserve Christian traditions, about the Last Supper, the arrest and the substitution at the cross, that are older than any Muslim book.';
	$index[] = '## On the sources and the translation';
	$index[] = 'The Arabic texts are the standard printed editions as published without restriction at tafsir.app, and, for Tanwīr al-Miqbās, at the Shamela library (shamela.ws, book 12877); all eight works are in the public domain. The English renderings were prepared for this website and are released with it; they have not been checked by a second translator, and the Arabic source of every passage is named so that it can be compared. The editors’ modern apparatus, their footnotes on manuscripts and chains, is not part of the classical text and is not translated. Corrections are welcome through the <a href="/contact/">contact page</a> and are recorded in the <a href="/corrections/">corrections log</a>.';
	$index[] = '## Questions about the commentaries';
	$index[] = '? What is tafsīr?';
	$index[] = 'Tafsīr is the Muslim discipline of explaining the Qur’an: the meaning of its words, the occasions on which its verses were revealed, the reports of the Prophet and his Companions about them, and the rulings and lessons drawn from them. The great commentaries run to many volumes; the pages here give what eight of them say on the verses about Jesus.';
	$index[] = '? Why translate the whole commentary and not the parts about Jesus?';
	$index[] = 'Because a selection is an argument, and this website wants the reader to see the record and not an argument about it. Every page carries all that the commentator wrote on the verse, including the grammar, the poetry and the chains of transmission, so that what he said about Jesus is read in the place and the company he gave it.';
	$index[] = '? Do the commentators agree that Jesus was not crucified?';
	$index[] = 'Yes. From al-Ṭabarī in the tenth century to al-Saʿdī in the twentieth, every commentary translated here reads 4:157 as the Qur’an states it: the Jews did not kill him and did not crucify him, but another was made to resemble him and God raised Jesus to Himself. They differ about who the other man was, whether a disciple who volunteered or the man sent to seize him, and about whether Jesus tasted death for some hours before he was raised. None of them says he died on the cross.';
	$index[] = '? Are these the “Israelite tales” Muslims are warned about?';
	$index[] = 'Some of the reports are. The commentators mark the stories that came through Kaʿb al-Aḥbār, Wahb ibn Munabbih and the People of the Book, and Ibn Kathīr in particular says they may be related but not relied on. They are translated here because they are part of what was said, and because the historian values them as early Christian traditions preserved in Arabic.';
	$index[] = '? Which translation of the Qur’an do the commentaries quote?';
	$index[] = 'None. The Qur’anic phrases within the commentaries are translated afresh with the commentary, since the commentator is often explaining the very word a translation would have to choose, and the reference is given so that the verse can be read in the site’s translation on <a href="/quran/passages/">the Qur’anic passages</a>.';
	$index[] = '? Why are there no Shia commentaries?';
	$index[] = 'The eight works here are the ones most read across the Sunni world and the ones the site owner asked for. The Shia commentaries, al-Ṭūsī’s <em>al-Tibyān</em> and al-Ṭabrisī’s <em>Majmaʿ al-bayān</em> above all, read these verses in the same way on the birth, the signs and the raising of Jesus, and may be added.';
	$pages[]  = array(
		'path'    => $base,
		'title'   => 'The classical commentaries on Jesus',
		'excerpt' => 'Eight classical commentaries on the Qur’an, translated in full on the verses that tell the story of Jesus: his birth, his signs, the cross and his raising.',
		'body'    => $index,
	);

	foreach ( $groups as $slug => $g ) {
		$body = array( $g['intro'] );
		$body[] = sprintf( 'The verses are on <a href="/quran/passages/">the Qur’anic passages</a>. Each commentary below is complete for %s; choose a work to read it, or read them in order from the earliest.', $g['title'] );
		$body[] = '## The commentaries on these verses';
		$links  = array();
		foreach ( $works as $key => $w ) {
			$entries = injil_help_tafsir_entries( $key, $g['surah'], $g['from'], $g['to'] );
			if ( ! $entries ) {
				continue;
			}
			$words = 0;
			foreach ( $entries as $e ) {
				$words += injil_help_tafsir_words( $e['paragraphs'] );
			}
			// Split into parts of about $limit words, never splitting an entry.
			$parts = array( array() );
			$acc   = 0;
			foreach ( $entries as $e ) {
				$n = injil_help_tafsir_words( $e['paragraphs'] );
				if ( $acc && $acc + $n > $limit ) {
					$parts[] = array();
					$acc     = 0;
				}
				$parts[ count( $parts ) - 1 ][] = $e;
				$acc += $n;
			}
			$total_parts = count( $parts );
			$wpath       = $base . '/' . $slug . '/' . $key;
			$links[]     = sprintf( '- <strong><a href="/%1$s/">%2$s</a></strong> (d. %3$s), about %4$s words%5$s.', $wpath, $w['short'], $w['died'], number_format_i18n( round( $words, -1 ) ), $total_parts > 1 ? sprintf( ', in %d parts', $total_parts ) : '' );
			foreach ( $parts as $i => $part ) {
				$no    = $i + 1;
				$path  = 1 === $no ? $wpath : $wpath . '/' . $no;
				$first = injil_help_tafsir_ref( $part[0] );
				$last  = injil_help_tafsir_ref( $part[ count( $part ) - 1 ] );
				$range = $first === $last ? $first : $first . ' to ' . $last;
				$pb    = array(
					sprintf( '<em>%1$s</em>, by %2$s (d. %3$s). %4$s', $w['title'], $w['author'], $w['died'], $w['about'] ),
					$total_parts > 1
						? sprintf( 'Part %1$d of %2$d: the commentary on %3$s. The parts follow the order of the verses; this one covers %4$s.', $no, $total_parts, $g['title'], $range )
						: sprintf( 'The complete commentary on %1$s, covering %2$s.', $g['title'], $range ),
				);
				foreach ( $part as $e ) {
					$pb = array_merge( $pb, injil_help_tafsir_entry_lines( $e ) );
				}
				$pb[] = '## About this text';
				$pb[] = injil_help_tafsir_colophon( $key );
				$nav  = array();
				if ( $no > 1 ) {
					$nav[] = sprintf( '<a href="/%1$s/">← Part %2$d</a>', 1 === $no - 1 ? $wpath : $wpath . '/' . ( $no - 1 ), $no - 1 );
				}
				$nav[] = sprintf( '<a href="/%1$s/%2$s/">%3$s: all commentaries</a>', $base, $slug, $g['title'] );
				if ( $no < $total_parts ) {
					$nav[] = sprintf( '<a href="/%1$s/%2$d/">Part %2$d →</a>', $wpath, $no + 1 );
				}
				$nav[]   = sprintf( '<a href="/%s/">The classical commentaries</a>', $base );
				$pb[]    = implode( ' · ', $nav );
				$pages[] = array(
					'path'    => $path,
					'title'   => sprintf( '%1$s on %2$s%3$s', $w['short'], $g['title'], $total_parts > 1 ? sprintf( ', part %d', $no ) : '' ),
					'excerpt' => sprintf( 'The complete commentary of %1$s (%2$s) on %3$s, translated into English%4$s.', $w['short'], $w['title'], $g['title'], $total_parts > 1 ? sprintf( ', part %d of %d', $no, $total_parts ) : '' ),
					'body'    => $pb,
					'parent'  => $base . '/' . $slug,
				);
			}
		}
		$body = array_merge( $body, $links );
		$body[] = '## Where these verses stand in the gospel';
		$secs   = array();
		foreach ( injil_help_tafsir_section_map() as $n => $ranges ) {
			foreach ( $ranges as $r ) {
				if ( $r[0] === $g['surah'] && $r[2] >= $g['from'] && $r[1] <= $g['to'] ) {
					$secs[ $n ] = sprintf( '<a href="/gospel-according-to-islam/%1$d/">Section %1$d</a> (%2$d:%3$d-%4$d)', $n, $r[0], $r[1], $r[2] );
				}
			}
		}
		$body[] = $secs ? 'The same commentaries are woven into ' . implode( ', ', $secs ) . ' of The Gospel According to Islam, at the point in the story where the verses are told.' : 'These verses are drawn on throughout The Gospel According to Islam.';
		$body[] = sprintf( '<a href="/%s/">← The classical commentaries</a>', $base );
		array_splice(
			$pages,
			1 + count( array_filter( $pages, function ( $p ) use ( $base ) {
				return 0 === strpos( $p['path'], $base . '/' );
			} ) ),
			0,
			array( array(
				'path'    => $base . '/' . $slug,
				'title'   => $g['title'],
				'excerpt' => sprintf( 'What the classical commentators say on %s, each in full.', $g['title'] ),
				'body'    => $body,
			) )
		);
	}
	// Parents must come before children: sort by path depth within the set, keeping the index first.
	usort(
		$pages,
		function ( $a, $b ) {
			return substr_count( $a['path'], '/' ) <=> substr_count( $b['path'], '/' );
		}
	);
	return $pages;
}

/** The "From the commentaries" lines for one gospel section, or an empty list. */
function injil_help_tafsir_section_lines( $n ) {
	$map = injil_help_tafsir_section_map();
	if ( empty( $map[ $n ] ) || ! injil_help_tafsir_available() ) {
		return array();
	}
	$works  = injil_help_tafsir_works();
	$groups = injil_help_tafsir_groups();
	$refs   = array();
	foreach ( $map[ $n ] as $r ) {
		$refs[] = $r[1] === $r[2] ? $r[0] . ':' . $r[1] : $r[0] . ':' . $r[1] . '-' . $r[2];
	}
	$lines   = array( '## From the commentaries on ' . implode( ', ', $refs ) );
	$lines[] = 'What the classical commentators wrote on the verses this section rests on, each given in full and folded away until opened. The same texts, with the other passages, are on <a href="/quran/commentaries/">the classical commentaries</a>.';
	$any     = false;
	foreach ( $works as $key => $w ) {
		$entries = array();
		foreach ( $map[ $n ] as $r ) {
			foreach ( injil_help_tafsir_entries( $key, $r[0], $r[1], $r[2] ) as $e ) {
				$entries[ $e['surah'] . ':' . $e['from'] . '-' . $e['to'] ] = $e;
			}
		}
		if ( ! $entries ) {
			continue;
		}
		$any    = true;
		$words  = 0;
		$group  = '';
		foreach ( $entries as $e ) {
			$words += injil_help_tafsir_words( $e['paragraphs'] );
		}
		foreach ( $groups as $slug => $g ) {
			if ( $g['surah'] === (int) reset( $entries )['surah'] ) {
				$group = $slug;
				break;
			}
		}
		$lines[] = sprintf( '[details] %1$s, <em>%2$s</em> (d. %3$s), about %4$s words', $w['short'], $w['title'], $w['died'], number_format_i18n( round( $words, -1 ) ) );
		foreach ( $entries as $e ) {
			$lines = array_merge( $lines, injil_help_tafsir_entry_lines( $e, '### ' ) );
		}
		if ( $group ) {
			$lines[] = sprintf( 'Read this commentary with the other passages on <a href="/quran/commentaries/%1$s/%2$s/">its own page</a>.', $group, $key );
		}
		$lines[] = '[/details]';
	}
	return $any ? $lines : array();
}
