<?php
/**
 * Sections from the Infancy Gospel of James and the Infancy Gospel of Thomas,
 * woven into the sections of The Gospel According to Islam. Keyed by section
 * number; each value is a list of page-markup lines. The summaries are this
 * website's own, prepared from Mark M. Mattison's public-domain translations
 * at gospels.net. Items that agree with, go beyond, or conflict with the
 * Qur'an carry the same colour marks as the Gospel of Thomas sections.
 *
 * @package injil-help
 */

defined( 'ABSPATH' ) || exit;

function injil_help_gospel_infancy_sections() {
	$james_intro = 'The Infancy Gospel of James tells of the birth and childhood of Mary. It is not scripture for Muslims or for most Christians. Its earliest manuscript is Papyrus Bodmer V, of the late third or early fourth century, and most scholars place the composition in the second century. Where it agrees with the Qur’an the item is marked green; where the Qur’an is silent it is marked blue; where the sense is uncertain it is marked gold. Summaries are this website’s own, from Mark M. Mattison’s public-domain translation at gospels.net.';
	$thomas_intro = 'The Infancy Gospel of Thomas, not to be confused with the Gospel of Thomas, tells stories of the boyhood of Jesus. It is not scripture for Muslims or for most Christians. Its earliest Greek manuscript, Hagios Saba 259, was copied in the eleventh century, though versions in other ancient languages are earlier. Its date of composition is uncertain. Summaries are this website’s own, from Mark M. Mattison’s public-domain translation at gospels.net.';
	$borrow       = 'These books are weighed by the same measure as the gospels. Whether the Qur’an took its stories from them is answered on <a href="/resources/borrowing/">Did the Qur’an borrow its stories?</a>';

	return array(
		'1' => array(
			'## From the Infancy Gospel of James',
			$james_intro,
			'- [agree] <strong>James 4.</strong> Mary’s mother, long childless, prays for a child and vows to give it to the Lord’s service.{{fn:Qur’an 3:35: the wife of Amran vows what is in her womb to God. The Qur’an gives the vow; the book adds the years of barrenness and names her parents Joachim and Anna.}}',
			'- [agree] <strong>James 7.</strong> At the age of three Mary is brought to the temple, where the priest welcomes her, and she lives there.{{fn:Qur’an 3:37: her Lord accepted her graciously and made her grow in purity. The Qur’an gives no age.}}',
			'- [agree] <strong>James 8.</strong> In the temple an angel feeds her.{{fn:Qur’an 3:37: each time Zechariah entered the sanctuary he found food with her, and she said it was from God.}}',
			'- [unclear] <strong>James 9.</strong> When she is older the priests decide who will take charge of her by a sign from staffs, and it falls to Joseph.{{fn:Qur’an 3:44 says those concerned cast their pens to decide who would take charge of Mary, and 3:37 puts her in Zechariah’s charge. The way of deciding is alike; the guardian differs. The Qur’an does not name Joseph.}}',
			'- [beyond] <strong>James 10.</strong> Mary is among the pure young women chosen to spin the thread for the temple veil.{{fn:Not in the Qur’an, which is silent. Compare Luke 1:26-27, which says nothing of it either.}}',
			'- [beyond] <strong>James 22-24.</strong> Herod seeks John, and Zechariah, refusing to say where the child is hidden, is killed at the temple.{{fn:The Qur’an does not say how Zechariah died. It names him among the righteous (6:85) and says that some prophets were killed unjustly (2:61; 3:21). Neither confirms nor denies this account.}}',
			$borrow,
		),
		'2' => array(
			'## From the Infancy Gospel of James',
			$james_intro,
			'- [agree] <strong>James 11.</strong> While Mary spins purple thread an angel tells her she will conceive by the Lord’s word and bear a son who will be named Jesus.{{fn:Qur’an 3:45; 19:19. Compare Luke 1:26-38.}}',
			'- [beyond] <strong>James 13-16.</strong> Joseph, finding her with child, is troubled; an angel reassures him in a dream, and the priests test them both and clear them.{{fn:The Qur’an has no Joseph. It records that Mary’s people accused her when she came back carrying the child (19:27-28). Compare Matthew 1:18-25.}}',
			'- [beyond] <strong>James 17-19.</strong> On the way to a census Joseph finds a cave, and Jesus is born there.{{fn:Qur’an 19:22-23: Mary withdrew to a remote place, and the pains of labour brought her to the trunk of a palm tree. The Qur’an says nothing of a cave, a census, an inn or a manger. Compare Luke 2:1-7.}}',
			'- [beyond] <strong>James 20.</strong> A midwife and Salome confirm the birth; Salome doubts Mary’s virginity, tests her, and has her hand healed.{{fn:Not in the Qur’an or in the canonical gospels. The Qur’an defends Mary through the speech of the infant (19:29-33), not through a test.}}',
			$borrow,
		),
		'4' => array(
			'## From the Infancy Gospel of Thomas',
			$thomas_intro,
			'- [beyond] <strong>Thomas 9, 10, 16-18.</strong> The boy Jesus calls a fallen neighbour back to life, heals a young man’s foot, heals James of a snakebite, and revives a child and a builder.{{fn:The Qur’an affirms that ʿĪsā healed and raised the dead, by God’s permission (3:49; 5:110), but names none of these cases.}}',
			'- [beyond] <strong>Thomas 6-7, 15.</strong> He sets out the alphabet for his first teacher, who admits he is out of his depth, and a third teacher recognises his wisdom.{{fn:Not in the Qur’an. Compare Luke 2:46-47, where the boy in the temple astonishes the teachers.}}',
			'The same book also has the boy curse a playmate so that he withers, and another so that he dies, strike his accusers blind (Thomas 3-5), and curse a teacher who struck him so that he dies (Thomas 14). These conflict with the Qur’an, where the infant ʿĪsā says God has made him blessed wherever he is, dutiful to his mother, and neither arrogant nor wretched (19:31-32). They are set aside.',
			$borrow,
		),
		'14' => array(
			'## From the Infancy Gospel of Thomas',
			$thomas_intro,
			'- [agree] <strong>Thomas 2.</strong> The boy Jesus shapes twelve sparrows from clay, claps his hands, and they fly away.{{fn:Qur’an 3:49 and 5:110: ʿĪsā shapes the form of a bird from clay, breathes into it, and it becomes a bird by God’s permission, as a sign to the Israelites. Here the sign belongs to the ministry, is done by God’s permission, and some who saw it called it plain magic (5:110). The book sets it in a child’s play on a Sabbath and does not mention God’s permission.}}',
			$borrow,
		),
	);
}
