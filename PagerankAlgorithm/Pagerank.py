import networkx as nx

PATH = "/Users/Nandhini/Documents/Courses/CSCI 572 - IR Fall 20/Assignment 4/solr-7.7.0/../LATIMES/latimes/"

g = nx.read_edgelist("edge_dist_new.txt", create_using=nx.DiGraph())
pagerank = nx.pagerank(g, alpha=0.85, personalization=None, max_iter=30, tol=1e-06, nstart=None, weight='weight',
                       dangling=None)
prs = set()
for file, pr in pagerank.items():
    prs.add(pr)

print("Max", max(prs))
print("Min", min(prs))

with open("pagerank_new.txt", "w") as pg_file:
    for file, pr in pagerank.items():
        pg_file.write(PATH+file + "=" + str(pr)+"\n")

    pg_file.close()
