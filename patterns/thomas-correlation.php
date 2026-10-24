<?php
/**
 * Title: Thomas and the synoptics: correlation of sayings order
 * Slug: injil-help/thomas-correlation
 * Categories: injil-help
 * Description: A statistical test of whether the order of sayings in the Gospel of Thomas follows the synoptic gospels.
 * Keywords: Thomas, synoptic, correlation, statistics, sayings order, independence
 */
?>
<!-- wp:paragraph -->
<p>Scholars have long asked whether the Gospel of Thomas used the synoptic gospels as sources. About a hundred sayings attributed to Jesus appear both in Thomas and in Matthew, Mark and Luke. If Thomas had copied them from the gospels, one might expect it to keep some of their order. This page reports a statistical test of that expectation: whether the order of the parallel sayings in Thomas matches their order in Matthew, Mark, Luke or Q.</p>
<!-- /wp:paragraph -->
<!-- wp:heading -->
<h2 class="wp-block-heading">What earlier scholars concluded</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>The test was made to put numbers on a conclusion that scholars had reached by careful reading. R. McL. Wilson wrote that “if numerous sayings in Thomas were identical with those in our Gospels, if they appeared in the same order, or if they appeared in a different order in such a way to suggest that they had been collected under topical heads, there would be grounds for assuming a direct relationship; but in fact it is a notable feature of these sayings that almost all show some variation from the canonical form, while the order is almost entirely different and indications of a topical arrangement are almost entirely lacking” (<em>Studies in the Gospel of Thomas</em>, London: Mowbray, 1960, p. 51). Stephen Patterson concluded that “a comparison of the order followed by the Gospel of Thomas on the one hand, and the synoptic gospels on the other, has turned up little that would support the theory that the former is dependent upon the latter” (<em>The Gospel of Thomas Within the Development of Early Christianity</em>, Claremont dissertation, 1988, p. 140).</p>
<!-- /wp:paragraph -->
<!-- wp:heading -->
<h2 class="wp-block-heading">The method</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Whether two lists of sayings are in the same order is a question of correlation, and a statistical procedure can answer it. The parallels were taken from the list compiled by Helmut Koester, which marks some as “doubtful”. Two sets were made: 110 sayings with the doubtful parallels and 100 without. The results below are for the set of 100. The results for the set of 110 were not significantly different.</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph -->
<p>The Pearson product-moment correlation was calculated with the Statistical Analysis System of the SAS Institute, run on a Digital VAX minicomputer. The tables use three figures:</p>
<!-- /wp:paragraph -->
<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li><strong>Degree of correlation (R)</strong> shows how closely the two orders follow a straight-line relationship. A value near 1 means the orders are alike; a value near 0 means they are unrelated.</li>
<!-- /wp:list-item --><!-- wp:list-item -->
<li><strong>Reliability (p)</strong> shows how likely it is that the figure arose by chance. The lower it is, the more likely the correlation is real. A value above .05 means the apparent correlation is not significant.</li>
<!-- /wp:list-item --><!-- wp:list-item -->
<li><strong>N</strong> is the number of pairs of sayings compared.</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->
<!-- wp:heading -->
<h2 class="wp-block-heading">First check: the method finds order where order exists</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>A method for finding agreement in order must first be shown to find it where it is known to exist. Table I compares the order of the parallel sayings in the gospels with one another. The sayings are only those that happen to occur in Thomas, so they are in effect a random list.</p>
<!-- /wp:paragraph -->
<!-- wp:table {"hasFixedLayout":false,"align":"wide","className":"is-style-discrepancy"} -->
<figure class="wp-block-table alignwide is-style-discrepancy"><table><thead><tr><th>Comparison</th><th>Degree of correlation (R)</th><th>Pairs of sayings (N)</th><th>Reliability (p)</th><th>Significant?</th></tr></thead><tbody><tr><td>Matthew parallels in Thomas, against Luke parallels in Thomas</td><td>.528</td><td>62</td><td>.0001</td><td>Yes</td></tr><tr><td>Matthew parallels in Thomas, against Mark parallels in Thomas</td><td>.876</td><td>29</td><td>.0001</td><td>Yes</td></tr><tr><td>Luke parallels in Thomas, against Mark parallels in Thomas</td><td>.709</td><td>25</td><td>.0001</td><td>Yes</td></tr><tr><td>Q as in Matthew, against Q as in Luke</td><td>.460</td><td>37</td><td>.004</td><td>Yes</td></tr></tbody></table></figure>
<!-- /wp:table -->
<!-- wp:paragraph -->
<p>Every correlation is significant, and none is likely to be chance. The order of the sayings in Matthew is closer to their order in Mark than Luke’s is, and the two forms of Q agree with one another less closely than either gospel agrees with Mark. The result shows that the method detects shared order when it is there.</p>
<!-- /wp:paragraph -->
<!-- wp:heading -->
<h2 class="wp-block-heading">The test: Thomas against the gospels</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Table II sets the order of the parallel sayings in Thomas against their order in each gospel and in each source behind them.</p>
<!-- /wp:paragraph -->
<!-- wp:table {"hasFixedLayout":false,"align":"wide","className":"is-style-discrepancy"} -->
<figure class="wp-block-table alignwide is-style-discrepancy"><table><thead><tr><th>Comparison</th><th>Degree of correlation (R)</th><th>Pairs of sayings (N)</th><th>Reliability (p)</th><th>Significant?</th></tr></thead><tbody><tr><td>Mark parallels: Thomas’ order against Mark’s order</td><td>.213</td><td>30</td><td>.258</td><td>No</td></tr><tr><td>All Matthew parallels: Thomas’ order against Matthew’s order</td><td>.088</td><td>82</td><td>.431</td><td>No</td></tr><tr><td>All Luke parallels: Thomas’ order against Luke’s order</td><td>.141</td><td>77</td><td>.221</td><td>No</td></tr><tr><td>Q as in Matthew: Thomas’ order against Q’s order</td><td>.062</td><td>37</td><td>.714</td><td>No</td></tr><tr><td>Q as in Luke: Thomas’ order against Q’s order</td><td>.074</td><td>37</td><td>.663</td><td>No</td></tr><tr><td>Mark parallels in Matthew: Thomas’ order against Matthew’s order</td><td>.084</td><td>25</td><td>.689</td><td>No</td></tr><tr><td>Mark parallels in Luke: Thomas’ order against Luke’s order</td><td>.170</td><td>25</td><td>.415</td><td>No</td></tr></tbody></table></figure>
<!-- /wp:table -->
<!-- wp:paragraph -->
<p>No correlation is significant. Every p value is above .05, and every R is close to zero. The order of the parallel sayings in Thomas bears no statistically significant relation to their order in Matthew, Mark, Luke, or any part of the material they share.</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph -->
<p>Without the doubtful parallels there are 100 sayings in Thomas to compare with 226 ordered sayings in the gospels: 82 in Matthew, 77 in Luke, 37 in Q and 30 in Mark. Four short runs of sayings stand in the same order in Thomas and in a gospel: Thomas 32 and 33b with Matthew 5:14b-15; Thomas 43c-45c with Q (Matthew 12:31-35; Luke 6:43-45); Thomas 65-66 with the parable of the tenants and the rejected stone (Mark 12:1-12 and parallels); and Thomas 92a and 93-94 with Matthew 7:6-7. Each may have its own explanation, but four is about the number that chance alone would produce.</p>
<!-- /wp:paragraph -->
<!-- wp:heading -->
<h2 class="wp-block-heading">What the result does and does not show</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Scribes who copy a text often adjust it to the order of a better-known text. Had Thomas been copied by scribes who knew the gospels, one would expect some such adjustment, and none is found. The lack of correlation, with the earlier judgments of Wilson and Patterson, is evidence that Thomas did not draw its sayings from the synoptic gospels.</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph -->
<p>Four limits should be kept in mind. First, the test concerns order only. A writer who knew the gospels could have rearranged them freely, and the main arguments for dependence rest on wording. Mark Goodacre, in <em>Thomas and the Gospels: The Case for Thomas’s Familiarity with the Synoptics</em> (Eerdmans, 2012), looks for signs of Matthew’s and Luke’s own editing in Thomas, and answers the argument from order by saying that a writer who knew the gospels need not have kept their order. Nicholas Perrin, in <em>Thomas and Tatian</em> (2002), argues that Thomas depends on Tatian’s Diatessaron. Second, the test depends on Koester’s list of parallels. Third, the figures are those reported in the note; this website has not rerun them, and Wilson’s and Patterson’s words are quoted as the note gives them. Patterson’s 1988 Claremont dissertation was revised and published as <em>The Gospel of Thomas and Jesus</em> (Polebridge Press, 1993). Fourth, independence, even if granted, gives Thomas no authority of its own: it makes a saying separately transmitted, not true.</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph -->
<p>For this website the Qur’an remains the measure. Sayings of Thomas that agree with it are used and marked; those that conflict are set aside. The result above bears only on how much weight Thomas carries as a witness to the sayings tradition. See <a href="/gospels/thomas/">the introduction to Thomas</a>, <a href="/gospels/synoptic-problem/">the synoptic problem</a> and <a href="/gospels/thomas/sayings/">the full text of Thomas</a>.</p>
<!-- /wp:paragraph -->
<!-- wp:heading -->
<h2 class="wp-block-heading">Credit</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>The analysis, the tables and the quotations from Wilson and Patterson come from the note “Correlation Analysis” on the order of sayings in the Gospel of Thomas. The list of parallels is Helmut Koester’s. This page restates the note in this website’s words and adds the opening, the plain-language reading of the figures, and the limits.</p>
<!-- /wp:paragraph -->
