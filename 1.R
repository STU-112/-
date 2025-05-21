rm(list = ls())
gc()

#
# usage function for all
#
setwd("C:\\Users\\User\\Desktop") 
x <- read.csv("mentalhealth_dataset中.csv", header = TRUE, fileEncoding = "Big5")
data<-x[,c(-1,-2,-4,-5)]
head(data)
set.seed(42)
kmeans_result <- kmeans(data, centers = 3, nstart = 20)
print(kmeans_result$cluster)

data$Cluster <- as.factor(kmeans_result$cluster)
ggplot(data, aes(x =  抑鬱, y = 年齡, color = Cluster)) + geom_point(size = 3) + labs(title = "K-Means Clustering on USArrests Dataset") + theme_minimal()
#欄位可以換成其他兩兩欄位

wss <- vector()
for (k in 1:10) {
  kmeans_model <- kmeans(data, centers = k, nstart = 10)
  wss[k] <- kmeans_model$tot.withinss
}

plot(1:10,wss,type="b",pch=19,xlab="number of clusters K",ylab="total within_cluster sum of squares",main="elbow method for data")



result <- kmeans(data, centers = 3, nstart = 20)
table(result$cluster)

numeric_data <- data[, sapply(data, is.numeric)]
pca_result <- prcomp(numeric_data, scale. = TRUE)

df <- data.frame(PC1 = pca_result$x[, 1],PC2 = pca_result$x[, 2],
                Cluster = as.factor(result$cluster),
                State = rownames(data))

ggplot(df, aes(x = PC1, y = PC2, color = Cluster, label = State)) +
  geom_point(size = 3) +
  geom_text(size = 3, vjust = -1) +
  labs(title = "K-Means Clustering on data (PCA Reduced)") +
  theme_minimal()

data1<-x[,c(-1,-2,-3,-4,-5,-6,-11,-12,-13,-14,-15,-16)]
install.packages("pheatmap")
library(pheatmap)
numeric_data <- data1[, sapply(data1, is.numeric)]
numeric_matrix <- as.matrix(numeric_data)
pheatmap(numeric_matrix, clustering_method = "ward.D2", main = "Heatmap with Hierarchical Clustering")



dist_matrix <- dist(numeric_data, method = "euclidean")
hc <- hclust(dist_matrix, method = "ward.D2")
plot(hc, labels = data$孤獨感分數, main = "Hierarchical Clustering Dendrogram", xlab = "Samples", sub = "")
rect.hclust(hc, k = 3, border = "red")



install.packages("dbscan")
library(dbscan)
set.seed(123)
data1<-x[,c(-1,-2,-3,-4,-5,-6,-13,-14,-15,-16)]
numeric_data <- data1[, sapply(data1, is.numeric)]
plot(numeric_data , main = "原始資料點")
dbscan_result <- dbscan(numeric_data, eps = 0.5, minPts = 5)
print(dbscan_result$cluster)
